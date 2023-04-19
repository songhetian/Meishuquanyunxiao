<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use backend\models\Admin;
use backend\models\CourseSearch;
use common\models\Course;
use common\models\CourseMaterial;
use common\models\Format;
use components\Excel;

/**
 * CourseController implements the CRUD actions for Course model.
 */
class CourseController extends Controller
{
    /**
     * Lists all Course models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExport()
    {
        $model = new Course();
        Excel::setTitleData(
            $model->attributeLabels(), 
            [
                'id',
                'status'
            ]
        );
        $searchModel = new CourseSearch();
        $data = $searchModel->search(Yii::$app->request->queryParams, -1);
        Excel::CreateExcel($data->getModels(), $model->className());
    }
    /**
     * Displays a single Course model.
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
     * Creates a new Course model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Course();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Course model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->started_at = date('Y/m/d', $model->started_at);
        $model->ended_at = date('Y/m/d', $model->ended_at);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Course model.
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
     * Finds the Course model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Course the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Course::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    //获取上课时间、所属班级所对应支持的开始时间
    public function actionGetStartedAt($class_period_id, $class_id, $id = 0)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return Course::getDisabledDates($class_period_id, $class_id, $id);
        }
    }

    //获取开始时间对应的结束时间
    public function actionGetEndedAt($class_period_id, $class_id, $started_at, $id = 0)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $started_at = strtotime($started_at);
            return [
                'mindate' => date('Y/m/d', $started_at),
                'maxDate' => Course::getMaxDate($class_period_id, $class_id, $started_at, $id)
            ];
        }
    }
}
