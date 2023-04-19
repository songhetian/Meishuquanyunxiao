<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\Activity;


class ActivityController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Activity';

    public function actionList()
    {
        $modelClass = $this->modelClass;

        $_GET['message'] = "获取活动成功";

        return $list = Activity::find()
        					     ->where(['status'=>10])
        					     ->orderBy("is_top desc,created_at desc")
        					     ->limit(5)
        					     ->all();

    }
}