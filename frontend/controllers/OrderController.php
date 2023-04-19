<?php
namespace frontend\controllers;

use Yii;
use common\alipay\aop\AopClient;
use common\alipay\aop\request\AlipayTradeAppPayRequest;
use common\models\UserVipPrice;
use yii\data\ActiveDataProvider;
/**
* 订单信息
*
*/
	class OrderController extends \yii\web\Controller
	{

		//返回会员价格信息
	    public function actionGetVipPrice(){
	    	error_reporting(0);
	        $res = UserVipPrice::find()->where(['status' => 1])->asArray()->all();

	        $data = array("success"=>true,"data"=>$res,"message"=>"获得成功");
	        return json_encode($data);
	    }


		//返回订单信息
	    public function actionOrderInfo(){

	    	$price = UserVipPrice::findOne(1);

			$aop = new AopClient;

			$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
			$aop->appId =  Yii::$app->params['alipay']['appId'];
			$aop->rsaPrivateKey = Yii::$app->params['alipay']['rsaPrivateKey'];
			$aop->format = "json";
			//$aop->charset = "UTF-8";	
			$aop->signType = "RSA2";
			
			$aop->alipayrsaPublicKey = Yii::$app->params['alipay']['alipayrsaPublicKey'];
			
			//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
			$request = new AlipayTradeAppPayRequest();
			//SDK已经封装掉了公共参数，这里只需要传入业务参数
			// $bizcontent = "{\"body\":\"我是测试数据\"," 
			//                 . "\"subject\": \"App支付测试\","
			//                 . "\"out_trade_no\": \"TEST".time()."\","
			//                 . "\"timeout_express\": \"30m\"," 
			//                 . "\"total_amount\": \"0.01\","
			//                 . "\"product_code\":\"QUICK_MSECURITY_PAY\""
			//                 . "}";
			$bizcontent = json_encode([
	            'body'=>"我是测试数据",  
	            'subject'=>'App支付测试',  
	            'out_trade_no'=>"TEST".time(),//此订单号为商户唯一订单号  
	            'total_amount'=> "0.01",//保留两位小数  
	            'product_code'=>'QUICK_MSECURITY_PAY'
	        ]);
			$request->setNotifyUrl("http://www.meishuquanyunxiao.com/order/order-check.html");
			$request->setBizContent($bizcontent);
			//这里和普通的接口调用不同，使用的是sdkExecute
			$response = $aop->sdkExecute($request);
			//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
			echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
			//echo $response;
		}

		//异步返回
		public function actionOrderCheck(){
			$aop = new AopClient;
			$aop->alipayrsaPublicKey = Yii::$app->params['alipayrsaPublicKey'];
			$flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
		}
	}

?>