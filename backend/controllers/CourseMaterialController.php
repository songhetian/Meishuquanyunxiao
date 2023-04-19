<?php

namespace backend\controllers;

use Yii;
use common\models\CourseMaterial;
use backend\models\CourseMaterialSearch;
use yii\web\NotFoundHttpException;
use common\models\Picture;
use common\models\Video;
use common\models\Curl;
use common\models\Gather;
use common\models\Format;

/**
 * CourseMaterialController implements the CRUD actions for CourseMaterial model.
 */
class CourseMaterialController extends Controller
{
    /**
     * Lists all CourseMaterial models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourseMaterialSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CourseMaterial model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'gather_id' => Yii::$app->session->getFlash('gather_id'),
        ]);
    }

    /**
     * Creates a new CourseMaterial model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($gather_id='')
    {
        $model = new CourseMaterial();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if($gather_id){
                $gather = Gather::findOne($gather_id);
                Yii::$app->session->setFlash('gather_id',$gather_id);
                $gather->course_material_id = Format::explodeValue(Format::addFilterString($gather->course_material_id,$model->id));
                
                $gather->save();
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CourseMaterial model.
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
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CourseMaterial model.
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
        $model->recoveryStatus();
        return $this->redirect(['index']);
    }

    /**
     * Finds the CourseMaterial model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CourseMaterial the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CourseMaterial::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    //获取素材数据
    public function actionGetMaterials($source, $type, $table, $tag, $field)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if($source == $table::SOURCE_LOCAL){
                $materials = CourseMaterial::getMaterials($table);
                return [
                    'material' => CourseMaterial::concatLocalFilters($materials, $table, $tag, $field),
                    'count' => $materials['count'],
                ];
            }else{
                $materials = CourseMaterial::getMetisMaterials($type);
                return [
                    'material' => CourseMaterial::concatFilters($type, $materials),
                    'count' => $materials['count']
                ];
            }
        }
    }

    //科目筛选
    public function actionMetisCategoryFilter($type, $category_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $res = CourseMaterial::getMetisMaterials($type, $category_id);
            return [
                'category_child' => CourseMaterial::concatCategoryChild($res['category_child']),
                'keyword' => CourseMaterial::concatKeyword($res['keyword']),
                'material' => CourseMaterial::concatMetisMaterial($type, $res),
                'count' => $res['count']
            ];
        }
    }
    
    //分类筛选
    public function actionMetisCategoryChildFilter($type, $category_id, $category_child_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $res = CourseMaterial::getMetisMaterials($type, $category_id, $category_child_id);
            return [
                'keyword' => CourseMaterial::concatKeyword($res['keyword']),
                'material' => CourseMaterial::concatMetisMaterial($type, $res),
                'count' => $res['count']
            ];
        }
    }

    //关键字筛选
    public function actionMetisKeywordFilter($type, $category_id, $category_child_id, $keyword_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if($keyword_id){
                $keyword_id = str_replace('-', ',', $keyword_id);
            }
            $res = CourseMaterial::getMetisMaterials($type, $category_id, $category_child_id, $keyword_id);
            return [
                'material' => CourseMaterial::concatMetisMaterial($type, $res, $keyword_id),
                'count' => $res['count']
            ];
        }
    }

    //出版社筛选
    public function actionMetisPublishingFilter($type, $category_id, $category_child_id, $keyword_id, $publishing_id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $res = CourseMaterial::getMetisMaterials($type, $category_id, $category_child_id, $keyword_id, $publishing_id);
            return [
                'material' => CourseMaterial::concatMetisMaterial($type, $res, $keyword_id, $publishing_id),
                'count' => $res['count']
            ];
        }
    }

    //本地素材库分页
    public function actionGetPage($page = 0, $table, $tag, $field)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $res = CourseMaterial::getMaterials($table, $page);
            return [
                'material' => CourseMaterial::concatLocalMaterial($res, $table, $tag, $field, $page)
            ];
        }
    }

    //美术圈分页
    public function actionGetMetisPage($page = 0, $type, $vid, $keyword_id = 0, $publishing_id = 0)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if($keyword_id){
                $keyword_id = str_replace('-', ',', $keyword_id);
            }
            $res = CourseMaterial::getMetisMaterials($type, 0, $vid, $keyword_id, $publishing_id, $page);
            return [
                'material' => CourseMaterial::concatMetisMaterial($type, $res, $keyword_id, $publishing_id)
            ];
        }
    }

    //美术圈搜索以及分页
    public function actionMetisSearch($type, $search, $page = 0)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $res = CourseMaterial::metisSearch($type, $search, $page);
            return [
                'material' => CourseMaterial::concatMetisMaterial($type, $res, 0, 0, $search),
                'count' => $res['count']
            ];
        }
    }

    //获取CC视频
    public function actionGetSpark($cc_id)
    {
        $object .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="400" id="cc_'.$cc_id.'">';
        $object .= '<param name="movie" value="https://p.bokecc.com/flash/single/8FF243A3955D1B18_'.$cc_id.'_false_512DF3C568A265EC_1/player.swf" />';
        $object .= '<param name="allowFullScreen" value="true" />';
        $object .= '<param name="allowScriptAccess" value="always" />';
        $object .= '<param value="transparent" name="wmode" />';
        $object .= '<embed src="https://p.bokecc.com/flash/single/8FF243A3955D1B18_'.$cc_id.'_false_512DF3C568A265EC_1/player.swf" width="100%" height="400" name="cc_'.$cc_id.'" allowFullScreen="true" wmode="transparent" allowScriptAccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"/>';
        $object .= '</object>';
        return $object;
    }

    //获取对应图片进行保存
    public function actionSaveImage($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = current(Curl::metis_file_get_contents(
                Yii::$app->params['metis']['Url']['commodity'].'?id='.$id
            ));
            $model = new Picture();
            $model->source = Picture::SOURCE_METIS;
            $model->name = ($data->name) ? $data->name : Yii::t('backend', 'Name Is Empty');
            $model->metis_material_id = $data->id;
            $model->publishing_company = $data->publishing_company;
            $model->keyword_id = $data->keyword_id;
            $model->image = $data->image;
            $model->category_id = $data->category_id;
            if($model->save()){
                $size = Yii::$app->params['oss']['Size']['250x250'];
                return [
                    'alt' => $model->id,
                    'src' =>$data->image . $size
                ];
            }
        }
    }

    //获取视频图片进行保存
    public function actionSavePreview($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = current(Curl::metis_file_get_contents(
                Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$id
            ));
            $model = new Video();
            $model->source = Video::SOURCE_METIS;
            $model->charging_option = $data->charging_option * 10;
            $model->name = ($data->title) ? $data->title : Yii::t('backend', 'Name Is Empty');
            $model->metis_material_id = $data->id;
            $model->studio_id = $data->studio_id;
            $category = current(explode(',', $data->category));
            $model->category_id = $category;
            $model->keyword_id = $data->keyword;
            $model->preview = $data->preview_image;
            $model->cc_id = $data->chapter;

            if($model->save()){
                $size = Yii::$app->params['oss']['Size']['250x250'];
                return [
                    'alt' => $model->id,
                    'src' => $data->preview_image . $size,
                    'cc_id' => $data->chapter
                ];
            }
        }
    }
}