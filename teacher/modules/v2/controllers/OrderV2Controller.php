<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\alipay\aop\AopClient;
use common\alipay\aop\request\AlipayTradeAppPayRequest;
use common\models\UserVipPrice;
use common\models\Classes;
use common\models\GoodsAlipayOrder;
use common\models\GoodsWechatOrder;
use teacher\modules\v2\models\ActivationCode;
use common\models\WechatOrder;
use common\models\IapOrder;
use teacher\modules\v2\models\Gather;
use common\models\GoodsIapOrder;
use teacher\modules\v2\models\BuyRecord;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\SimpleUser;
use teacher\modules\v2\models\Family;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use common\models\Curl;
use common\models\BuyClasses;
use common\models\Campus;
/**
* 订单信息
*
*/
	class OrderV2Controller extends MainController
	{

		public $modelClass = 'teacher\modules\v2\models\Admin';
		//返回会员价格信息
	    public function actionGetVipPrice(){
	    	error_reporting(0);
	        $res = UserVipPrice::find()->where(['status' => 1])->asArray()->all();

	        $data = array("success"=>true,"data"=>$res,"message"=>"获得成功");
	        return $data;
	    }
		//返回订单信息
	    public function actionOrderInfo(){
	    	if(!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type'))){
	    		if(Yii::$app->request->get('goods_type')=='course'){
	    			$course = Curl::metis_file_get_contents(
			            'www.meishuquan.net/rest/course-v2/get-course?course_id='.Yii::$app->request->get('goods_id')
			        );
			        $courseinfo = $course[0];
			        $title = $courseinfo->title;
	    		}

	    		if(Yii::$app->request->get('goods_type')=='courseware'){
    				$course = Gather::find()->where(['id'=>Yii::$app->request->get('goods_id')])->one();
			        $courseinfo = $course;
			        $title = $courseinfo->name;
				}
				
				if(Yii::$app->request->get('goods_type')=='class_course'){
					$course = Classes::find()->where(['id'=>Yii::$app->request->get('goods_id')])->one();
					$courseinfo = $course;
					$title = $courseinfo->name;
				}
				
				if(empty($courseinfo)){
					echo "类型错误";
					exit;
				}

	    		$price = floatval($courseinfo->price);

	    		//测试账号价格
	    		if(Yii::$app->request->get('user_id') == 4025 && Yii::$app->request->get('user_type')=='teacher'){
	    			$price = 0.01;
	    		}
	    		if(Yii::$app->request->get('user_id') == 4276 && Yii::$app->request->get('user_type')=='teacher'){
	    			$price = 0.01;
	    		}

				$aop = new AopClient;
				$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
				$aop->appId =  Yii::$app->params['alipay']['appId'];
				$aop->rsaPrivateKey = Yii::$app->params['alipay']['rsaPrivateKey'];
				$aop->format = "json";
				//$aop->charset = "UTF-8";
				$aop->signType = "RSA2";
				$aop->alipayrsaPublicKey = Yii::$app->params['alipay']['alipayrsaPublicKey'];
				
				$out_trade_no = "AL".Yii::$app->request->get('user_id').Yii::$app->request->get('user_type').time().mt_rand(100, 999);;

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
		            'body'=>$title,
		            'subject'=>$title,  
		            'out_trade_no'=> $out_trade_no,//此订单号为商户唯一订单号  
		            'total_amount'=> number_format($price,2,".",""),//保留两位小数
		            'product_code'=>'QUICK_MSECURITY_PAY'
		        ]);
				$request->setNotifyUrl("https://api.teacher.meishuquanyunxiao.com/v2/order-v2/order-check");
				$request->setBizContent($bizcontent);


				//var_dump($request);
				//这里和普通的接口调用不同，使用的是sdkExecute
				$response = $aop->sdkExecute($request);
				//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
				//echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。


				$order = new GoodsAlipayOrder;
				$order->user_id = Yii::$app->request->get('user_id');
				$order->user_type = Yii::$app->request->get('user_type');
				$order->out_trade_no = $out_trade_no;
				$order->price = number_format($price,2,".","");
				$order->subject = $title;
				$order->goods_id = Yii::$app->request->get('goods_id');
				$order->goods_type = Yii::$app->request->get('goods_type');
				if($order->save()){
					$data = array("success"=>true,"data"=>$response,"message"=>"获得成功");
	        		return $data;
				}else{
					var_dump($order->getErrors());
					var_dump(number_format($price,2));
				}
				echo "订单生成失败";
				
				//echo $response;
	    	}else{
	    		echo "参数有误";
	    	}
	    	
		}

		//异步返回
		public function actionOrderCheck(){
			$aop = new AopClient;
			$aop->alipayrsaPublicKey = Yii::$app->params['alipay']['alipayrsaPublicKey'];
			$flag = $aop->rsaCheckV2($_POST, NULL, "RSA2");

			if($_POST['trade_status'] == 'TRADE_SUCCESS'){
				$order = GoodsAlipayOrder::findOne(['out_trade_no' => $_POST['out_trade_no'],'status'=>2]);
				if(!empty($order)){
	                $order->load(Yii::$app->getRequest()->getBodyParams(), '');
					$order->status = 1;
	            	if($order->save()){

	            		if($order->goods_type=='course'){
			    			$course = Curl::metis_file_get_contents(
					            'www.meishuquan.net/rest/course-v2/get-course?course_id='.$order->goods_id
					        );
					        if($course[0]->goods_pay_type != 0){
			            		$this->addvip($order->user_id,$order->user_type,$course[0]->goods_pay_type);
			            	}
							echo 'success';
		            	}

		            	if($order->goods_type=='courseware'){
		    				$course = Gather::find()->where(['id'=>$order->goods_id])->one();
			    			$buy = new BuyRecord();

			    			if($order->user_type=="teacher"){
			    				$buy_studio = Admin::findOne($order->user_id);
			    			}else{
								$buy_studio = User::findOne($order->user_id);
			    			}

			    			$buy->buy_id = $order->user_id;
		        			$buy->buy_studio = $buy_studio->studio_id;
		        			$buy->gather_id = $order->goods_id;

		        			$gather_studio = Admin::findOne($course->admin_id);

		        			$buy->gather_studio = $gather_studio->studio_id;
		        			$buy->admin_id = $course->admin_id;
		        			$buy->created_at = time();
		        			$buy->updated_at = time();
		        			$buy->active_at =  time()+(60*60*24*365);
		        			$buy->price = $course->price;
		        			$buy->role = $order->user_type=="teacher"?10:20;
		        			$buy->status = 10;
		        			$buy->save();
						}
						
						if($order->goods_type=='class_course'){
		    				$course = Classes::find()->where(['id'=>$order->goods_id])->one();
							$buy = new BuyClasses();

							if($order->user_type=="teacher"){
								$buy_studio = Admin::findOne($order->user_id);
							}else{
								$buy_studio = User::findOne($order->user_id);
							}

							$buy->buy_id = $order->user_id;
							$buy->buy_studio = $buy_studio->studio_id;
							$buy->classes_id = $order->goods_id;

							$classes_studio = Campus::findOne($course->campus_id)->studio_id;;

							$buy->classes_studio = $classes_studio->studio_id;
							$buy->created_at = time();
							$buy->updated_at = time();
							$buy->active_at =  time()+(60*60*24*365*3);
							$buy->price = $course->price;
							$buy->role = $order->user_type=="teacher"?10:20;
							$buy->status = 10;
							$buy->save();
			    		}
		    		}
				}
				echo 'success';
			}
			$file  = './allog.txt';
			ob_start();
			var_dump($_POST);
			var_dump($flag);
			var_dump($order->getErrors());
			$content=ob_get_clean();
			if($f  = file_put_contents($file, $content,FILE_APPEND)){}
		}

	public function actionWxorderInfo(){
		if(!empty(Yii::$app->request->get('goods_id')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('goods_type'))){
			if(Yii::$app->request->get('goods_type')=='course'){
    			$course = Curl::metis_file_get_contents(
		            'www.meishuquan.net/rest/course-v2/get-course?course_id='.Yii::$app->request->get('goods_id')
		        );
		        $courseinfo = $course[0];
		        $title = $courseinfo->title;
    		}

			if(Yii::$app->request->get('goods_type')=='courseware'){

    			$course = Gather::find()->where(['id'=>Yii::$app->request->get('goods_id')])->one();
		        $courseinfo = $course;
		        $title = $courseinfo->name;
    		}
			
			if(Yii::$app->request->get('goods_type')=='class_course'){
				$course = Classes::find()->where(['id'=>Yii::$app->request->get('goods_id')])->one();
				$courseinfo = $course;
				$title = $courseinfo->name;
			}

    		$price = $courseinfo->price;

    		if(Yii::$app->request->get('user_id') == 4025 && Yii::$app->request->get('user_type')=='teacher'){
    			$price = 0.01;
    		}
    		if(Yii::$app->request->get('user_id') == 4276 && Yii::$app->request->get('user_type')=='teacher'){
	    			$price = 0.01;
	    		}
			$out_trade_no = "WXVIP".Yii::$app->request->get('user_id').Yii::$app->request->get('user_type').time().mt_rand(100, 999);
			return  $this->weChatPay($out_trade_no,$price,$title,Yii::$app->request->get('user_id'),Yii::$app->request->get('user_type'),Yii::$app->request->get('goods_id'),Yii::$app->request->get('goods_type'));
		}
	}
	public function weChatPay($order_num,$price,$name,$user_id,$user_type,$goods_id,$goods_type){
		$json = array();
		//生成预支付交易单的必选参数:
		$newPara = array();
		//应用ID
		$newPara["appid"] = Yii::$app->params['weixin']['appid'];
		//商户号
		$newPara["mch_id"] = Yii::$app->params['weixin']['mch_id'];
		//设备号
		$newPara["device_info"] = "WEB";
		//随机字符串,这里推荐使用函数生成
		$newPara["nonce_str"] = $this->createNoncestr();
		//商品描述
		$newPara["body"] = $name;
		//商户订单号,这里是商户自己的内部的订单号
		$newPara["out_trade_no"] = $order_num;
		//总金额
		$newPara["total_fee"] = $price*100;
		//终端IP
		$newPara["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
		//通知地址，注意，这里的url里面不要加参数
		$newPara["notify_url"] = "http://api.teacher.meishuquanyunxiao.com/v2/order-v2/wxorder-check";
		//交易类型
		$newPara["trade_type"] = "APP";
		$key = Yii::$app->params['weixin']['key'];
		//第一次签名
		$newPara["sign"] = $this->appgetSign($newPara,$key);
		//把数组转化成xml格式
		$xmlData = $this->arrayToXml($newPara);
		$get_data = $this->sendPrePayCurl($xmlData);
		//返回的结果进行判断。
		if($get_data['return_code'] == "SUCCESS" && $get_data['result_code'] == "SUCCESS"){
			//根据微信支付返回的结果进行二次签名
			//二次签名所需的随机字符串
			$newPara["nonce_str"] = $this->createNoncestr();
			//二次签名所需的时间戳
			$newPara['timeStamp'] = time()."";
			//二次签名剩余参数的补充
			$secondSignArray = array(
				"appid"=>$newPara['appid'],
				"noncestr"=>$newPara['nonce_str'],
				"package"=>"Sign=WXPay",
				"prepayid"=>$get_data['prepay_id'],
				"partnerid"=>$newPara['mch_id'],
				"timestamp"=>$newPara['timeStamp'],
			);
			$json['success'] = true;
			$json['data']['ordersn'] = $newPara["out_trade_no"];//订单号
			$json['data']['order_arr'] = $secondSignArray;//返给前台APP的预支付订单信息
			$json['data']['order_arr']['sign'] = $this->appgetSign($secondSignArray,$key);//预支付订单签名
			$json['message'] = "预支付完成";
			//预支付完成,在下方进行自己内部的业务逻辑
			/*****************************/
			$order = new GoodsWechatOrder;
			$order->user_id = $user_id;
			$order->product_id = $goods_id;
			$order->user_type = $user_type;
			$order->goods_type = $goods_type;
			$order->time_start = date("YmdHis",time());
			$order->load($newPara, '');
			$order->status = 2;
			$order->save();
			return $json;
		}else{
			$json['success'] = false;
			$json['message'] = $get_data['return_msg']; 
			return $json; 
		}
	}
	//返回地址
	public function actionWxorderCheck(){
		header("Content-type: text/xml");
		$file  = './wxlog.txt';
		//接收传送的数据
		$fileContent = file_get_contents("php://input");
		### 把xml转换为数组
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		//先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组。
		$value_array = json_decode(json_encode(simplexml_load_string($fileContent, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

		if($value_array['return_code'] == "SUCCESS" && $value_array['result_code'] == "SUCCESS"){
			$order = GoodsWechatOrder::findOne(['out_trade_no' => $value_array['out_trade_no']]);
			//未付款状态的订单
			if(!empty($order) && $order->status == 2){
				$order->time_expire = $value_array['time_end'];
				$order->status = 1;
            	$order->save();

            	if($order->goods_type=='course'){
	    			$course = Curl::metis_file_get_contents(
			            'www.meishuquan.net/rest/course-v2/get-course?course_id='.$order->product_id
			        );
			       	$file  = './allog.txt';
					ob_start();
					var_dump($course[0]->goods_pay_type);
					$content=ob_get_clean();
					if($f  = file_put_contents($file, $content,FILE_APPEND)){}

					if($course[0]->goods_pay_type != 0){
	            		$this->addvip($order->user_id,$order->user_type,$course[0]->goods_pay_type);
	            	}
	    		}
	    		if($order->goods_type=='courseware'){
    				$course = Gather::find()->where(['id'=>$order->product_id])->one();
	    			$buy = new BuyRecord();

	    			if($order->user_type == "teacher"){
	    				$buy_studio = Admin::findOne($order->user_id);
	    			}else{
						$buy_studio = User::findOne($order->user_id);
	    			}

	    			$buy->buy_id = $order->user_id;
        			$buy->buy_studio = $buy_studio->studio_id;
        			$buy->gather_id = $order->product_id;

        			$gather_studio = Admin::findOne($course->admin_id);

        			$buy->gather_studio = $gather_studio->studio_id;
        			$buy->admin_id = $course->admin_id;
        			$buy->created_at = time();
        			$buy->updated_at = time();
        			$buy->active_at =  time()+(60*60*24*365);
        			$buy->price = $course->price;
        			$buy->role = $order->user_type=="teacher"?10:20;
        			$buy->status = 10;
        			$buy->save();
				}
				
				if($order->goods_type=='class_course'){
					$course = Classes::find()->where(['id'=>$order->product_id])->one();
					$buy = new BuyClasses();

					if($order->user_type=="teacher"){
						$buy_studio = Admin::findOne($order->user_id);
					}else{
						$buy_studio = User::findOne($order->user_id);
					}

					$buy->buy_id = $order->user_id;
					$buy->buy_studio = $buy_studio->studio_id;
					$buy->classes_id = $order->product_id;

					$classes_studio = Campus::findOne($course->campus_id)->studio_id;;

					$buy->classes_studio = $classes_studio->studio_id;
					$buy->created_at = time();
					$buy->updated_at = time();
					$buy->active_at =  time()+(60*60*24*365*3);
					$buy->price = $course->price;
					$buy->role = $order->user_type=="teacher"?10:20;
					$buy->status = 10;
					$buy->save();
				}

			}
		}
		$res = $this->arrayToXml(array("return_code"=>"SUCCESS","return_msg"=>"OK"));
		echo $res;
	}
 	
	//将数组转换为xml格式 
	public function arrayToXml($arr){
		$xml = "<xml>";
		foreach ($arr as $key=>$val){
			if(is_numeric($val)){
				$xml.="<".$key.">".$val."</".$key.">";
			}else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$xml.="</xml>";
		return $xml;
	}
	//发送请求
	public function sendPrePayCurl($xml,$second=30){
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		curl_close($ch);
		$data_xml_arr =$this->XMLDataParse($data);
		if($data_xml_arr){
			return $data_xml_arr;
		}else{
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	//xml格式数据解析函数
	public function XMLDataParse($data){
		$xml = simplexml_load_string($data,NULL,LIBXML_NOCDATA);
		$array=json_decode(json_encode($xml),true);
		return $array;
	}
	//随机字符串
	public function createNoncestr($length=32){
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for($i=0;$i<$length;$i++){ 
			$str.=substr($chars,mt_rand(0,strlen($chars)-1),1);  
		}  
		return $str;
	}
	/*
	 * 格式化参数格式化成url参数  生成签名sign
	*/
	public function appgetSign($Obj,$appwxpay_key){
		foreach ($Obj as $k => $v){
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
 		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		if($appwxpay_key){
			$String = $String."&key=".$appwxpay_key;
		}
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}
	//按字典序排序参数
	public function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach($paraMap as $k => $v){
			if($urlencode){
				$v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if(strlen($buff) > 0){
			$reqPar = substr($buff, 0, strlen($buff)-1);
 		}
		return $reqPar;
	}



	public function actionIosIapPay() {

		//user_id user_role time
        $status = array('status'=>-1,'success'=>true);
        //获取 App 发送过来的数据,设置时候是沙盒状态
        $receipt   = $_POST['receipt_data'];
        $isSandbox = false;
        //开始执行验证
        try
        {
            $info = $this->getReceiptData($receipt, $isSandbox);
            // 通过product_id 来判断是下载哪个资源
            // switch($_POST['goods_type']){
            //     case 'course':
                    $status['status'] = 1;
            //         //Header("Location:xxxx.zip");
            //         break;
            //     case 'ebook':
            //         $status['status'] = 1;
            //         //Header("Location:xxxx.zip");
            //         break;
            //     case 'course-cha':
            //         $status['status'] = 1;
            //         //Header("Location:xxxx.zip");
            //         break;
            // }


            if ($status['status']==1) {
            	$order = GoodsIapOrder::findOne(['transaction_id' => $info['transaction_id']]);
            	if(empty($order)){
					$iap_order = new GoodsIapOrder;
					$iap_order->user_id = $_POST['user_id'];
					$iap_order->user_type = $_POST['user_role'];
					$iap_order->goods_id = $_POST['goods_id'];
					$iap_order->goods_type = $_POST['goods_type'];
					$iap_order->product_id = $info['product_id'];
					$iap_order->quantity = $info['quantity'];
					$iap_order->transaction_id = $info['transaction_id'];
					$iap_order->purchase_date = $info['purchase_date'];
					$iap_order->app_item_id = $info['app_item_id'];
					$iap_order->create_time = date("Y-m-d H:i:m",time());
					$iap_order->save();

					if($iap_order->goods_type=='course'){
		    			$course = Curl::metis_file_get_contents(
				            'www.meishuquan.net/rest/course-v2/get-course?course_id='.$iap_order->goods_id
				        );
				        if($course[0]->goods_pay_type != 0){
			    			$this->addvip($iap_order->user_id,$iap_order->user_type,$course[0]->goods_pay_type);
			    		}
		    		}


		    		if($iap_order->goods_type=='courseware'){
		    			$course = Gather::find()->where(['id'=>$iap_order->goods_id])->one();
		    			$buy = new BuyRecord();


		    			if($iap_order->user_type=="teacher"){
		    				$buy_studio = Admin::findOne($iap_order->user_id);
		    			}else{
							$buy_studio = User::findOne($iap_order->user_id);
		    			}
		    			$buy->buy_id = $iap_order->user_id;
            			$buy->buy_studio = $buy_studio->studio_id;
            			$buy->gather_id = $iap_order->goods_id;

            			$gather_studio = Admin::findOne($course->admin_id);
            			$buy->admin_id = $course->admin_id;
            			$buy->gather_studio = $gather_studio->studio_id;
            			$buy->created_at = time();
            			$buy->updated_at = time();
            			$buy->active_at =  time()+(60*60*24*365);
            			$buy->price = $course->price;
            			$buy->role = $iap_order->user_type=="teacher"?10:20;
            			$buy->status = 10;

            			$buy->save();
		    		}

					if($iap_order->goods_type=='class_course'){
		    			$course = Classes::find()->where(['id'=>$iap_order->goods_id])->one();
		    			$buy = new BuyClasses();


		    			if($iap_order->user_type=="teacher"){
		    				$buy_studio = Admin::findOne($iap_order->user_id);
		    			}else{
							$buy_studio = User::findOne($iap_order->user_id);
		    			}
		    			$buy->buy_id = $iap_order->user_id;
            			$buy->buy_studio = $buy_studio->studio_id;
            			$buy->classes_id = $iap_order->goods_id;

            			$classes_studio = Campus::findOne($course->campus_id)->studio_id;
            			$buy->classes_studio = $classes_studio->studio_id;
            			$buy->created_at = time();
            			$buy->updated_at = time();
            			$buy->active_at =  time()+(60*60*24*365*3);
            			$buy->price = $course->price;
            			$buy->role = $iap_order->user_type=="teacher"?10:20;
            			$buy->status = 10;

            			$buy->save();
		    		}
		    		
					// $file  = './iaplog.txt';
					// ob_start();
					// var_dump($info);
					// var_dump($iap_order->getErrors());
					// $content=ob_get_clean();
					// if($f  = file_put_contents($file, $content,FILE_APPEND)){}

            	}else{
            		$status['status'] = -2;//已存在订单
            	}
            }
			// $file  = './iaplog.txt';
			//  ob_start();
			// // var_dump($status);
			// var_dump($_POST);
			// var_dump($info);
			// // echo date("Y-m-d H:i:m",time());
			//  $content=ob_get_clean();
			//  if($f  = file_put_contents($file, $content,FILE_APPEND)){}
            return $status;
        }
            //捕获异常
        catch(\Exception $e)
        {
        	$file  = './iaplog.txt';
			ob_start();
			var_dump($status);
			//var_dump($_POST);
			var_dump($info);
			$content=ob_get_clean();
			if($f  = file_put_contents($file, $content,FILE_APPEND)){}
			$status['success'] = false;
            return $status;
        }
    }


    //服务器二次验证代码
    function getReceiptData($receipt, $isSandbox = false)
    {
        if ($isSandbox) {
            $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
        }
        else {
            $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
        }

        $postData = json_encode(array("receipt-data" => $receipt));

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);


        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);
        curl_close($ch);
        //判断时候出错，抛出异常
        if ($errno != 0) {
            throw new \Exception($errmsg, $errno);
        }

        $data = json_decode($response);

        //此处是看到先人们的指导，又看到apple的官方说法改的。否则会审核不过貌似是审核也会走沙盒测试者，
        //此处先判断一次返回的status是否=21007 这数据是从测试环境，但它发送到生产环境中进行验证。它发送到测试环境来代替。
        if ($data->status == 21007) {
            $this->getReceiptData($receipt,true);
            return;
        }
        //判断返回的数据是否是对象
        if (!is_object($data)) {
            throw new \Exception('Invalid response data');
        }
        //判断购买时候成功
        if (!isset($data->status) || $data->status != 0) {
            throw new \Exception('Invalid receipt');
        }

        //返回产品的信息
        $ascinapp = $data->receipt->in_app;


        //var_dump($ascinapp);exit;
		foreach($ascinapp as $value){
	    	$flag[]=$value->transaction_id;
	    }
	    array_multisort($flag, SORT_ASC, $ascinapp);
		$in_app = end($ascinapp);

        //返回产品的信息
		return (
		  array(
		   'quantity' => $in_app->quantity,   
		   'product_id' => $in_app->product_id,  
		   'transaction_id' => $in_app->transaction_id, 
		   'purchase_date' => $in_app->purchase_date, 
		   'app_item_id' => $data->receipt->app_item_id,
		   'original_transaction_id'=>$in_app->original_transaction_id,
		   'in_app'=>$data->receipt->in_app,
		   'ascinapp'=>$ascinapp)
		);
    }



    function addvip($user_id,$user_type,$type){
    	if($user_type == "teacher"){
			$user = Admin::findOne(['id'=>$user_id]);
			$temp = 1;
		}else if($user_type == "student"){
			$user = User::findOne(['id'=>$user_id]);
			$temp = 2;
		}else if($user_type == "family"){
			$user = Family::findOne(['id'=>$user_id]);
			$temp = 999;
		}

		$time = date('Y-m-d',time());

		if(!empty($user->vip_time) && strtotime($user->vip_time) > strtotime($time)){
			$time = $user->vip_time;
		}
		$code = ActivationCode::findOne(
				[
				 'relation_id'      =>  $user->id,
				 'type'	=> $temp,
				]
			);
		if(!empty($code)){
			if(!empty($code->due_time) && strtotime($code->due_time) > strtotime($time)){
				$time = $code->due_time;
			}
			switch($type){
	            case '1':
	            	$time = date('Y-m-d',strtotime('+1 month',strtotime($time)));
	                break;
	            case '2':
	                $time  = date('Y-m-d',strtotime('+3 month',strtotime($time)));
	                break;
	            case '3':
	                $time  = date('Y-m-d',strtotime('+6 month',strtotime($time)));
	                break;
	            case '4':
	                $time  = date('Y-m-d',strtotime('+12 month',strtotime($time)));
	                break;
	            case '5':
	                $time  = date('Y-m-d',strtotime('+7 days',strtotime($time)));
	                break;
	        }
			$code->due_time = $time;
			$code->save();
			$user->vip_time = $time;
			$user->save();
		}else{
			switch($type){
	            case '1':
	            	$time = date('Y-m-d',strtotime('+1 month',strtotime($time)));
	                break;
	            case '2':
	                $time  = date('Y-m-d',strtotime('+3 month',strtotime($time)));
	                break;
	            case '3':
	                $time  = date('Y-m-d',strtotime('+6 month',strtotime($time)));
	                break;
	            case '4':
	                $time  = date('Y-m-d',strtotime('+12 month',strtotime($time)));
	                break;
	            case '5':
	                $time  = date('Y-m-d',strtotime('+7 days',strtotime($time)));
	                break;
	        }
			$user->vip_time = $time;
			$user->save();
		}
    }


    public function actionCeshiaddvip($user_id,$user_type,$goods_id){

    			    			$course = Gather::find()->where(['id'=>$goods_id])->one();
		    			$buy = new BuyRecord();


		    			if($user_type=="teacher"){
		    				$buy_studio = Admin::findOne($user_id);
		    			}else{
							$buy_studio = User::findOne($user_id);
		    			}
		    			$buy->buy_id = $user_id;
            			$buy->buy_studio = $buy_studio->studio_id;
            			$buy->gather_id = $goods_id;

            			$gather_studio = Admin::findOne($course->admin_id);
            			$buy->gather_studio = $gather_studio->studio_id;
            			$buy->created_at = time();
            			$buy->updated_at = time();
            			$buy->active_at =  time()+(60*60*24*365);
            			$buy->price = $course->price;
            			$buy->role = $user_type=="teacher"?10:20;
            			$buy->status = 10;

            			$buy->save();
    }
}
?>