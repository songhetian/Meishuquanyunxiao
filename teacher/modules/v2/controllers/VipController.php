<?php 
namespace teacher\modules\v2\controllers;

define("TOKEN", 123456);

use Yii;
use components\Oss;
use components\Upload;
use common\models\Format;
use components\ImageHandle;
use yii\base\ErrorException;
use abei2017\wx\Application;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Family;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\ActivationCode;

class VipController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Admin';


	//生成小程序码
	public function  actionIndex($scene = '', $page = 'mySub/pages/loginDetail/loginDetail') {
		error_reporting(0);
		if($this->user_role == 10) {
			$model = Admin::findOne($this->user_id);
		}elseif($this->user_role == 20) {
			$model = User::findOne($this->user_id);
		}elseif($this->user_role == 30) {
			$model = Family::findOne($this->user_id);
		}
		
		$size = Yii::$app->params['oss']['Size']['general'];

		if($model->qrcode) {

			$_GET['message'] = "生成成功";
			
			return Oss::getUrl1('qrcode',$model->qrcode).$size;
		}else{

			$qrcode = (new Application())->driver("mini.qrcode");
	   		
	   		$scene = "1&".$this->user_id."&".$this->user_role;
	   		
			$name =  $this->createQrcode($scene,$page);

			$model->qrcode = $name;

			$model->save();

			$_GET['message'] = "生成成功";

			return Oss::getUrl1('qrcode',$model->qrcode).$size;

		}
	}


	//生成open_id 
	public function actionGetOpenId($code) {

		$User = (new Application())->driver("mini.user");

		$_GET['message'] = "获取成功";
		
		return $User->codeToSession($code);
	}

	//接收小程序信息
	public function actionGetContent() {
		
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            return 11111;
        }

	}
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.TOKEN;
            exit;
        }
    }
 private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
 
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
 
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }


	//检测画室是否超过购买数量
	public function actionCheck() {
		$studios = Studio::find()
						   ->select('id,name,teacher_num,one_year_num,two_years_num,three_years_num')
						   ->where(['status'=>10])
						   ->asArray()
						   ->all();
		foreach ($studios as $key => $value) {
			$int = 0;
			if(!in_array($value['id'], array(43,103,183))) {
				$teacher = ActivationCode::find()
										   ->where(['status'=>10,'studio_id'=>$value['id'],'type'=>1])
										   #->andWhere(['>','due_time',date("Y-m-d",time())])
										   ->count();
				if($value['teacher_num'] < $teacher) {
					$int = $teacher-$value['teacher_num'];
					$error[$value['name']] = array('teacher'=>"老师数量超过$int");
				}
				$one = ActivationCode::find()
							   ->where(['status'=>10,'studio_id'=>$value['id'],'type'=>2,'activetime'=>1])
							   ->count();
				if($value['one_year_num'] < $one) {
					$int = $one-$value['one_year_num'];
					$error[$value['name']] = array('one_year_num'=>"一年学生数量超过$int");
				}
				$two = ActivationCode::find()
							   ->where(['status'=>10,'studio_id'=>$value['id'],'type'=>2,'activetime'=>2])
							   ->count();
				if($value['two_years_num'] < $two) {
					$int = $two-$value['two_years_num'];
					$error[$value['name']] = array('two_years_num'=>"2年学生数量超过$int");
				}
				$three = ActivationCode::find()
							   ->where(['status'=>10,'studio_id'=>$value['id'],'type'=>2,'activetime'=>3])
							   ->count();
				if($value['three_years_num'] < $three) {
					$int = $three-$value['three_years_num'];
					$error[$value['name']] = array('three_years_num'=>"3年学生数量超过$int");
				}
			}
		}

		return $error;
	}

    public function createQrcode($scene, $page)
    {
    	//生成小程序二维码
		$qrcode = (new Application())->driver("mini.qrcode");
   		$binary = $qrcode->unLimit($scene,$page);
   		//将二进制保存成图片
   		$dir = dirname(Yii::$app->BasePath).'/teacher/web/upload/qrcode/';
   		$name = $this->user_type.$this->user_id.".jpg";
   		$file_name = $dir.$name;
        $file = fopen($file_name, "w");
        fwrite($file, $binary);
        //二维码合并
        $bg = dirname(Yii::$app->BasePath)."/teacher/web/upload/bg.jpg";

        if($this->user_role == 10) {
        	$UserName = Admin::findOne($this->user_id)->name;
        }elseif($this->user_role == 20){
        	$UserName = User::findOne($this->user_id)->name;
        }elseif($this->user_role == 30) {
        	$UserName = Family::findOne($this->user_id)->name;
        }


        ImageHandle::Create($bg,$file_name,dirname(Yii::$app->BasePath)."/teacher/web/upload/",$UserName,$name);

        fclose($file);
        //上传OSS
        $ossKey = 'qrcode/'.$name;	

        $codeDir = dirname(Yii::$app->BasePath).'/teacher/web/upload/'.$name;

        Upload::UploadQrcode($ossKey,$codeDir);          
        //删除本地图片
        @unlink($codeDir);
        @unlink($file_name);
        return $name;
    }

}