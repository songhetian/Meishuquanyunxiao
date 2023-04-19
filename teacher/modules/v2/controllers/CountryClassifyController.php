<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;


class CountryClassifyController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\CountryClassify';

    public function actionGetList()
    {
        $modelClass = $this->modelClass;

        $list = $modelClass::find()
                             ->where(['status'=>10])
                             ->all();

        $_GET['message'] = "获取列表成功";

        $info = array();
        foreach ($list as $key => $value) {
            $info[$value['name']] = $value['schools'];
        }

        return array('院校报考'=>$info);
    }
}