<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use backend\models\UserSearch;
use common\models\User;
use common\models\City;
use common\models\Classes;
use common\models\Family;
use common\models\ActivationCode;
use components\Excel;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();

        Yii::$app->session->setFlash('queryParams',Yii::$app->request->queryParams);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExport()
    {
        $model = new User();
        
        $labels = $model->attributeLabels();
        $labels['code_name'] = '激活码';
        $labels['code_time'] = '剩余时间';
        Excel::setTitleData(
            $labels, 
            [
                'id',
                'student_id',
                'campus_id',
                #'phone_number',
                'phone_verify_code',
                'password_hash',
                'gender',
                'national_id',
                'family_member_name',
                'relationship',
                'organization',
                'position',
                'contact_phone',
                'race',
                'student_type',
                'image',
                'is_image',
                'career_pursuit_type',
                'residence_type',
                'grade',
                'province',
                'city',
                'detailed_address',
                'qq_number',
                'school_name',
                'united_exam_province',
                'fine_art_instructor',
                'exam_participant_number',
                'sketch_score',
                'color_score',
                'quick_sketch_score',
                'design_score',
                'verbal_score',
                'math_score',
                'english_score',
                'total_score',
                'pre_school_assessment',
                'is_graduation',
                'graduation_at',
                'is_all_visible',
                'note',
                'auth_key',
                'password_reset_token',
                'device_token',
                'access_token',
                'admin_id',
               # 'created_at',
                'updated_at',
                'is_review',
                'status',
            ]
        );
        $searchModel = new UserSearch();
        if(Yii::$app->session->getFlash('queryParams')){
            $data = $searchModel->search(Yii::$app->session->getFlash('queryParams'), -1);
        }else{
            $data = $searchModel->search(Yii::$app->request->queryParams, -1);
        }
        Excel::CreateExcel($data->getModels(), $model->className(), 2);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->setScenario('update');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionIsAllVisible($id)
    {
        $model = $this->findModel($id);
        if($model->isAllVisible()){
            return $this->redirect(['index']);
        }
    }

    public function actionIsReview($id)
    {
        $model = $this->findModel($id);
        if($model->is_review == $model::REVIEW_NOT_YET){
            if(!$model::isNumberOfReviewFull()){
                return true;
            }
        }
        if($model->isReview()){
            return $this->redirect(['index']);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        $code = ActivationCode::findOne(['relation_id'=>$id,'type'=>2]);

        if($code) {
            $model->name     = NULL;
            $model->image    = NULL;
            $model->is_image = NULL;
            $model->phone_number = NULL;
        }else{
            $model->status = 0;
            $code->is_active = ActivationCode::USE_DELETED;
        }
        //获取家长
        $family = Family::findOne(['relation_id'=>$id,'status'=>10]);

        $connect = Yii::$app->db->beginTransaction();

        try{
            if(!$model->save(false)) {
                throw new ErrorException(Errors::getInfo($model->getErrors())); 
            }

            if($family) {
                $family->relation_id = NULL;
                $family->token_value = NULL;
                if(!$family->save()){
                    throw new ErrorException(Errors::getInfo($family->getErrors()));   
                }
            }

            if($code) {
                if(!$code->save()) {
                    throw new ErrorException(Errors::getInfo($teacher->getErrors()));   
                }
            }
            $connect->commit();
            return $this->redirect(['index']);
        } catch (ErrorException $e) {
            $connect->rollBack();
            return $this->redirect(['index']);
        } 

        // if($model->updateStatus()){
        //     return $this->redirect(['index']);
        // }
    }
    /**
     * recovery an existing Classes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionRecovery($id)
    {
        $model = $this->findModel($id);
        if($model->recoveryStatus()){
            return $this->redirect(['index']);
        } 
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //获取省份对应城市
    public function actionGetCity($pid)
    {
        $model = City::getCityList($pid);
        foreach($model as $id => $name)
        {
            echo Html::tag('option', Html::encode($name), ['value' => $id]);
        }
    }

    /**
      * 获取班级信息
      * date 2017-05-31
      * author 田鹤松
      * @param integer $campus_id
      * @return mixed
    */
    public function actionClassesList($campus_id)
    {   
        $model = Classes::getClassesList($campus_id);
        echo Html::renderSelectOptions($model, $model);
    }
}
