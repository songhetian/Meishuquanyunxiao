<?php

namespace backend\controllers;

use Yii;
use common\models\SouceGroup;
use backend\models\SouceGroupSearch;
use yii\web\NotFoundHttpException;

/**
 * SouceGroupController implements the CRUD actions for SouceGroup model.
 */
class SouceGroupController extends Controller
{
    /**
     * Lists all SouceGroup models.
     * @return mixed
     */
    public function actionIndex($type = NULL)
    {
        if($type){
            Yii::$app->session->set('type', $type);
        }
        $searchModel = new SouceGroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single SouceGroup model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id)
        ]);
    }

    /**
     * Creates a new SouceGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SouceGroup();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model
            ]);
        }
    }

    /**
     * Updates an existing SouceGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model
            ]);
        }
    }

    /**
     * Deletes an existing SouceGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->is_main == SouceGroup::NOT_MAIN){
            $model->updateStatus();
        }
        return $this->redirect(['index']);
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
        $model->recoveryStatus();
        return $this->redirect(['index']);
    }

    public function actionIsPublic($id)
    {
        $model = $this->findModel($id);
        
        if($model->isPublic()){
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the SouceGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SouceGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SouceGroup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
