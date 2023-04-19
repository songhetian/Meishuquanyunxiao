<?php
namespace frontend\controllers;

use Yii;
use common\models\Studio;
use common\models\App;
use components\Oss;

class DownloadController extends \yii\web\Controller
{
    //下载页
    public function actionIndex($token_value){
       $studio = Studio::findOne(['token_value' => $token_value, 'status' => Studio::STATUS_ACTIVE]);
       $model = App::findOne(['studio_id' => $studio->id, 'status' => App::STATUS_ACTIVE]);
       $fileName = $token_value.'.png';
       return $this->render('index', [
       		'qrcode' => OSS::getUrl($model->studio_id, 'download', 'app', $fileName).Yii::$app->params['oss']['Size']['original'],
            'model' => $model,
       ]);
    } 

    public function actionTest(){

        return $this->render('test');
    } 
}