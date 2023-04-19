<?php

namespace backend\controllers;

use Yii;
use common\models\Campus;
use backend\models\CampusSearch;
use yii\web\NotFoundHttpException;
use components\Oss;

/**
 * CampusController implements the CRUD actions for Campus model.
 */
class CampusController extends Controller
{
    /**
     * Lists all Campus models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CampusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Campus model.
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
     * Creates a new Campus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Campus();

        if ($model->load(Yii::$app->request->post())) {
            if(!empty(Yii::$app->request->post('latLng'))){
                $latLng = explode(',',Yii::$app->request->post('latLng')); 
                $model->lat = $latLng[0];
                $model->lng = $latLng[1];
            }
            if ($model->save()){
                $images = Oss::uploads($model, $model->studio_id, 'registration', 'pic');
                if(!empty($images)){
                    $model->pic = $images[0];
                }
                if ($model->save()){
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Campus model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        $oldpic = $model->pic;
        if ($model->load(Yii::$app->request->post())) {
            $latLng=explode(',',Yii::$app->request->post('latLng')); 
            $model->lat = $latLng[0];
            $model->lng = $latLng[1];
            $images = Oss::uploads($model, $model->studio_id, 'registration', 'pic');
            if(!empty($images) && !empty($images[0])){
                $model->pic = $images[0];
            }else{
                $model->pic = $oldpic;
            }
            if ($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Deletes an existing Campus model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->is_main == Campus::MAIN_NOT_YET){
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

    /**
     * Finds the Campus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Campus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Campus::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
