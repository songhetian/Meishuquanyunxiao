<?php
namespace api\modules\v1\controllers;

use Yii;
use common\models\Curl;
use components\Upload;
use components\WXBizDataCrypt;
use components\Oss;

class LiveController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Live';
	
    //申请成为主播
    public function actionCreate()
    {
       //获取参数 访问美术圈注册接口
       return Curl::metis_file_post_contents(
	       Yii::$app->params['metis']['Url']['register'],
	       Yii::$app->getRequest()->getBodyParams()
       );
    }
    
    //上传身份证图片
    public function actionUpload()
    {
      //$_GET['message'] = "图片上传成功";
      $image = Upload::pic_upload($_FILES, 'live', 'live', 'file')['file']; 
      print(OSS::getUrl('live', 'live', 'file', $image).Yii::$app->params['oss']['Size']['original'] . "\n");
    }
    
    public function actionWxData()
    {
      $appid = 'wx54cc97755d7ecee0';

      $sessionKey = str_replace(' ','+',urldecode(Yii::$app->request->post('sessionKey')));
      $encryptedData = str_replace(' ','+',urldecode(Yii::$app->request->post('encryptedData')));
      $iv = str_replace(' ','+',urldecode(Yii::$app->request->post('iv')));

      $pc = new WXBizDataCrypt();
      $errCode = $pc->decryptData($appid, $sessionKey,$encryptedData, $iv, $data );
      if ($errCode == 0) {
          print($data . "\n");
      } else {
          print($errCode . "\n");
      }
    }

}