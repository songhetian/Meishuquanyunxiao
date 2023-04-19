<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use components\Oss;
use backend\models\Admin;
use backend\models\PictureSearch;
use common\models\Picture;
use common\models\Campus;
use common\models\Keyword;
use common\models\SouceGroup;
use common\models\Format;

/**
 * PictureController implements the CRUD actions for Picture model.
 */
class PictureController extends Controller
{
    /**
     * Lists all Picture models.
     * @return mixed
     */
    public function actionIndex($type = NULL, $gid = NULL)
    {
        if($type == SouceGroup::TYPE_PICTURE){
            return $this->redirect([
                'souce-group/index',
                'type' => $type
            ]);
        }

        if($gid){
            Yii::$app->session->set('gid', $gid);
        }

        $searchModel = new PictureSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Picture model.
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
     * Creates a new Picture model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Picture();
    
        if ($model->load(Yii::$app->request->post())) {
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'picture', 'image');

            $ids = [];
            foreach ($images as $image) {
                $_model = clone $model;
                $_model->image = $image;
                $_model->save();
                $ids[] = $_model->getPrimaryKey();
            }
            //增加分组数据
            $exps = implode($ids, ',');

            $group =  Yii::$app->request->post('Picture')['group'];
            $g = SouceGroup::findOne($group);
            $g->material_library_id .= ($g->material_library_id) ? ','.$exps : $exps;
            $g->save();

            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model
            ]);
        }
    }

    /**
     * Updates an existing Picture model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model->keyword_id){
            $model->keyword_id = Format::explodeValue($model->keyword_id);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Picture model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->updateStatus()){
            return $this->redirect(['index']);
        } 
    }
    
    /**
     * recovery an existing Admin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionRecovery($id)
    {
        $model = $this->findModel($id);
       // $model = $model->convertField();
        $model->recoveryStatus();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Picture model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Picture the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Picture::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    //获取分类对应关键字
    public function actionGetKeyword($category_id)
    {
        $model = Keyword::getKeywordList($category_id);
        foreach($model as $id => $name)
        {
            echo Html::tag('option', Html::encode($name), ['value' => $id]);
        }
    }
}
