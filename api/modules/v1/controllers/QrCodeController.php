<?php
namespace api\modules\v1\controllers;

use Yii;
use common\models\Curl;

class QrCodeController extends MainController
{
    public $modelClass = 'api\modules\v1\models\QrCode';

	public function actionScan($id)
    {
    	$modelClass = $this->modelClass;

    	$_GET['message'] = Yii::t('api', 'Sucessfully Get List');

    	return $modelClass::getChapters($id);  
    }
}