<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use backend\models\Admin;
use backend\models\AdminSearch;
use common\models\Format;
use common\models\Classes;
use components\Excel;

/**
 * AdminController implements the CRUD actions for Admin model.
 */
class AdminController extends Controller
{
    /**
     * Lists all Admin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdminSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExport()
    {
        $model = new Admin();
        $labels = $model->attributeLabels();
        $labels['code_name'] = '激活码';
        $labels['code_time'] = '剩余时间';
        Excel::setTitleData(
            $labels,
            [
                'id',
                'auth_key',
                'password_hash',
                'password_reset_token',
                'is_main',
                'is_sell',
                'sell_num',
                'status'
            ]
        );
        $searchModel = new AdminSearch();
        $data = $searchModel->search(Yii::$app->request->queryParams, -1);
        Excel::CreateExcel($data->getModels(), $model->className(), 1);
    }

    /**
     * Displays a single Admin model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $role = Yii::$app->authManager->getRolesByUser($id);
        $model->role = $role[key($role)]->description;

        return $this->render('view', [
            'model' => $model
        ]);
    }

    /**
     * Creates a new Admin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Admin(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Admin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $model->setScenario('update');
        
        $model = $model->convertField();

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
        $model = $model->convertField();
        
        if($model->isAllVisible()){
            return $this->redirect(['index']);
        }
    }

    /**
     * Deletes an existing Admin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model = $model->convertField();
        if($model->is_main == Admin::MAIN_NOT_YET){
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
        $model = $model->convertField();
        $model->recoveryStatus();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Admin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Admin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Admin::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    //获取校区对应班级
    public function actionGetClass($campus_id)
    {
        $model = Classes::getClassesList($campus_id);
        echo Html::renderSelectOptions($model, $model);
    }
}