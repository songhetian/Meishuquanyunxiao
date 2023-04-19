<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;


class SchoolController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\School';

    public function actionGetCategory()
    {
        return Yii::$app->params['Category'];
    }

    public function actionGetInfo($id) {

        $modelClass = $this->modelClass;
        $modelClass::$is_show = 1;

        $info = $modelClass::find()->where(['id'=>$id,'status'=>10])->one();
        $_GET['message'] = "获取成功";
        
        return $info;
    }

    public function findModel($id) {

        $modelClass = $this->modelClass;

        return $modelClass::findOne($id);
    }
}