<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use components\Spark;
use backend\models\VideoSearch;
use common\models\Video;
use common\models\Category;
use common\models\Keyword;
use common\models\SouceGroup;
use common\models\Format;

/**
 * VideoController implements the CRUD actions for Video model.
 */
class VideoController extends Controller
{
    /**
     * Lists all Video models.
     * @return mixed
     */
    public function actionIndex($type = NULL, $gid = NULL)
    {
        if($type == SouceGroup::TYPE_VIDEO){
            return $this->redirect([
                'souce-group/index',
                'type' => $type
            ]);
        }

        if($gid){
            Yii::$app->session->set('gid', $gid);
        }

        $searchModel = new VideoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Video model.
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
     * Creates a new Video model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Video();

        if ($model->load(Yii::$app->request->post())) {
            if($model->save()){
                $id = $model->getPrimaryKey();
                //增加分组数据
                $g = SouceGroup::findOne($model->group);
                $g->material_library_id .= ($g->material_library_id) ? ',' . $id : $id;
                $g->save();

                return $this->redirect(['view', 'id' => $model->id]);
            }
            
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Video model.
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
     * Deletes an existing Video model.
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
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    //获取分类对应关键字
    public function actionGetKeyword($category_id)
    {
        $model = Keyword::getKeywordList($category_id, Keyword::TYPE_VIDEO);
        foreach($model as $id => $name)
        {
            echo Html::tag('option', Html::encode($name), ['value' => $id]);
        }
    }

    //上传CC视频
    //public function actionGetUploadUrl($title, $tag, $description){
    public function actionGetUploadUrl($title, $description){
        $type = 'normal';
        //默认上传加密视频
        $info = [
            'title' => $title,
            //'tag' => Category::findOne($tag)->name,
            'description' => $description,
            'userid' => Yii::$app->params['spark'][$type]['userid']
        ];
        $time = time();
        $salt = Yii::$app->params['spark'][$type]['key'];
        $request_url = Spark::get_hashed_query_string($info, $time, $salt);
        echo $request_url;
    }
}
