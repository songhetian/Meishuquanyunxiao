<?php

namespace backend\controllers;

use Yii;
use components\Oss;
use backend\models\Admin;
use common\models\Gather;
use common\models\Campus;
use common\models\Format;
use yii\filters\VerbFilter;
use backend\models\GatherSearch;
use yii\web\NotFoundHttpException;
use backend\models\CourseMaterialSearch1;



/**
 * GatherController implements the CRUD actions for Gather model.
 */
class GatherController extends Controller
{
    /**
     * Lists all Gather models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new GatherSearch();

        $admin_id = Yii::$app->user->identity->id;
        $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$studio);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Gather model.
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
     * Creates a new Gather model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Gather();
        if ($model->load(Yii::$app->request->post())) {

            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $image = Oss::upload($model, $studio, 'picture', 'image');
            $model->setScenario('create');
            $model->image = $image;
            $model->studio_id = $studio;
            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Gather model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->course_material_id = Format::explodeValue($model->course_material_id);
        $image = $model->image;
        if ($model->load(Yii::$app->request->post())) {
            if($_FILES['Gather']['error']['image'] == 0){
                $admin_id = Yii::$app->user->identity->id;
                $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
                $image = Oss::upload($model, $studio, 'picture', 'image');
                $model->image = $image;
            }else{
                $model->image = $image;
            }
            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /*
     *[获取课件包详情]
     *
     *
    */
    public function actionList($id) {

        $ids = $this->findModel($id)['course_material_id'];
        $ids =  $ids ? $ids : "-1";
        $searchModel = new CourseMaterialSearch1();

        Yii::$app->session->setFlash('gather_id',$id);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,Format::explodeValue($ids));
        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'gather_id'    => $id,
        ]);
    }

    /**
     * Deletes an existing Gather model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        error_reporting(0);
        $model = $this->findModel($id);
        $model->course_material_id = Format::explodeValue($model->course_material_id);
        if($model->updateStatus()){
            return $this->redirect(['index']);
        } 
    }


    /**
     * 删除课件包内部课件
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id integer gather_id
     * @return mixed
     */
    public function actionListDelete($gather_id,$id)
    {
        error_reporting(0);
        $model = $this->findModel($gather_id);
        $model->course_material_id =  Format::explodeValue(Format::deleteFilterString($model->course_material_id,$id));
        if($model->save()){
            return $this->redirect(['index']);
        } 
    }

    /**
     * Finds the Gather model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Gather the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Gather::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
