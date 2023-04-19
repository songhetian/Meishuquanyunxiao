<?php

namespace backend\controllers;

use Yii;
use backend\models\Admin;
use common\models\User;
use common\models\Campus;
use common\models\BuyRecord;
use common\models\Gather;
use common\models\Format;
use common\models\Studio;
use yii\web\NotFoundHttpException;
use backend\models\BuyRecordSearch;


/**
 * BuyRecordController implements the CRUD actions for BuyRecord model.
 */
class BuyRecordController extends Controller
{
    /**
     * Lists all BuyRecord models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BuyRecordSearch();

        $studio_id = Campus::findOne(Yii::$app->user->identity->campus_id)->studio_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$studio_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BuyRecord model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if($model->role == 10){
            $model->buy_id     = Admin::findOne($model->buy_id)->name;
        }elseif($model->role == 20) {
            $model->buy_id     = User::findOne($model->buy_id)->name;
        }
        $model->buy_studio = Studio::findOne($model->buy_studio)->name;
        $model->gather_id  = Gather::findOne($model->gather_id)->name;
        $model->gather_studio  = Studio::findOne($model->gather_studio)->name;
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new BuyRecord model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BuyRecord();

        if ($model->load(Yii::$app->request->post())) {
            //处理购买
            $model->HandleBuy();

            if(!$model->buy_id) {
                $model->addError('code', "该用户不存在!");
                return $this->render('create', [
                    'model' => $model,
                ]);     
            }

            $int = Gather::findOne(['id'=>$model->gather_id])->activetime;

            $role = $model->role;

            $buy_recode = BuyRecord::findOne(['buy_id'=>$model->buy_id,'role'=>$role,'gather_id'=>$model->gather_id,'status'=>BuyRecord::STATUS_ACTIVE]);

            // if($role == 10){
            //     $buy_recode = BuyRecord::findOne(['buy_studio'=>$model->buy_studio,'gather_id'=>$model->gather_id,'status'=>BuyRecord::STATUS_ACTIVE]);
            // }elseif($role == 20){
            //     $buy_recode = BuyRecord::findOne(['buy_id'=>$model->buy_id,'gather_id'=>$model->gather_id,'status'=>BuyRecord::STATUS_ACTIVE]);
            // }
            if($buy_recode){
                $buy_recode->active_at = Format::addYears($buy_recode->active_at,$int);
                $buy_recode->price  += $model->price;
                $model = $buy_recode;
            }else{
                $model->active_at = Format::addYears(time(),$int);
            }

            
            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }else{
                $model->addError('code', "该用户不存在!");
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing BuyRecord model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model->role == 10) {
            $model->code = Admin::findOne($model->buy_id)->name;
            $model->role = BuyRecord::getValues('role',10);
        }elseif($model->role == 20) {
            $model->code = User::findOne($model->buy_id)->name;
            $model->role = BuyRecord::getValues('role',20);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing BuyRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the BuyRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BuyRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BuyRecord::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
