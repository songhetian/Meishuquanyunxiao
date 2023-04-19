<?php
namespace api\modules\v1\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Curl;
class WxController extends MainController
{

  public $modelClass = 'common\models\News';
  private $appid = 'wx54cc97755d7ecee0';  
  private $secret = '3319215e8fedec75f169e5062f6a8068';
  private $grant_type = 'authorization_code';

  /** 
   * 利用code获取session_key以及openid 
   * @param $code 
   * @return array 
   */  
  public function actionGetWechatInfoByCode(){
    if(!empty(Yii::$app->request->post('code')) ){
    $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appid.'&secret='.$this->secret.'&js_code='.Yii::$app->request->post('code').'&grant_type='.$this->grant_type;
    $http = Curl::file_post_contents($url);
    $res = yii\helpers\ArrayHelper::toArray(json_decode($http));
      return [
          'success' => true,
          'data' => $res,
          'http' => $http,
          'message' => '获取数据成功'
        ];
    }else{
      return [
          'success' => false,
          'message' => '非POST传值或缺少参数'
        ];
    }
  } 
}