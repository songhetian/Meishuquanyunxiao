<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;


class ArticleController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Article';

    public function actionGetList()
    {
        $modelClass = $this->modelClass;

        $school_id = Yii::$app->request->post('school_id');
        $id        = Yii::$app->request->post('id');
        $list      = $modelClass::find()
                                  ->select('id,title,time')
                                  ->where(['pid'=>$school_id,'classify_id'=>$id,'status'=>10])
                                  ->all();

        $_GET['message'] = "获取列表成功";

        return $list;
    }

    //详情
    public function actionGetInfo()
    {
        $modelClass = $this->modelClass;
        $id        = Yii::$app->request->post('id');
        $list      = $modelClass::find()
                                 # ->select('id,title,time')
                                  ->where(['id'=>$id,'status'=>10])
                                  ->all();

        $_GET['message'] = "获取列表成功";

        return $list;
    }
}