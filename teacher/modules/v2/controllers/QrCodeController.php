<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Curl;
use teacher\modules\v2\models\Studio;
use Da\QrCode\QrCode;
use components\Oss;

class QrCodeController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\QrCode';

	public function actionScan($id)
    {
    	$modelClass = $this->modelClass;

    	$_GET['message'] = Yii::t('api', 'Sucessfully Get List');

    	return $modelClass::getChapters($id);  
    }

    public function actionDownApp()
    {
        $studio_id = Yii::$app->request->post('studio_id');
        if(!$studio_id){
            return '缺少参数'; 
        }
        $studio = Studio::findOne($studio_id);
        $url = urldecode(Yii::$app->params['downUrl'].$studio->token_value);
		
		$_GET['message'] = Yii::t('teacher','Sucessfully Get Date');
		$res = [
			'name' => $studio->name.'-云校2.0',
			'desc' => '让更多的学生实现艺术梦',
			'logo' => urldecode(OSS::getUrl($studio->id, 'download', 'app', 'logo.png').Yii::$app->params['oss']['Size']['512x512']),
			'downurl' => $url,
		];
		return $res;
    }

    public function actionGenerate()
    {
    	$studio_id = Yii::$app->request->post('studio_id');
        if(!$studio_id){
            return '缺少参数'; 
        }

        $studio = Studio::findOne($studio_id);
        $url = urldecode(Yii::$app->params['downUrl'].$studio->token_value);
    	$qrCode = (new QrCode($url))
	    ->setSize(300)
	    ->setMargin(5);
	    
	    $fileName = $studio->token_value.'.png';
	    $filePath = Yii::getAlias('@backend').'/web/assets/qrcode/'.$fileName;
		
		$qrCode->writeFile($filePath);
		Oss::ossUpload($filePath, $studio->id, 'download', 'app', $fileName);
		$res = [
			'qrcode' => urldecode(OSS::getUrl($studio->id, 'download', 'app', $fileName).Yii::$app->params['oss']['Size']['original'])
		];
		return $res;
    }
}