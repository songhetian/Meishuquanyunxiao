<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use components\Oss;
use components\Code;
use components\Alidayu;
use yii\base\ErrorException;
use common\models\Format;
use components\Upload;
use teacher\modules\v2\models\City;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\SimpleUser;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Family;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Tencat;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\MailList;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;
use teacher\modules\v2\models\NewActivationCode;


class ShareController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Admin';

	//修改资料
	public function actionUpdate() {

		$id = Yii::$app->request->post('id');

	}

	public function actionTest($identifier) {

		return Tencat::createImage1($identifier);
	}

	//完善信息
	public function actionPerfect() {
		error_reporting(0); 
		$id          = Yii::$app->request->post('admin_id');

		$user_role   = Yii::$app->request->post('user_role');

		if(Yii::$app->request->post('bind_code')) {

			$BindCode  =  strtoupper(Yii::$app->request->post('bind_code'));

			$studio_id = Studio::findOne(['bind_code'=>$BindCode])->id;

			if(!$studio_id) {

				return SendMessage::sendErrorMsg('画室码错误!');
			}
		}

		if($user_role == Yii::$app->params['teacherRole']) {
			$model = Admin::findOne($id);

        	$studio = $model->studio_id;

		}else if($user_role == Yii::$app->params['studentRole']){
			$model = User::findOne($id);
			if(Yii::$app->request->post('bind_code')) {
				$model->studio_id = $studio_id;
			}

			$studio = $model->studio_id;
		}else if($user_role == Yii::$app->params['familyRole']){
			$model = Family::findOne($id);
			if(Yii::$app->request->post('bind_code')) {
				$model->studio_id = $studio_id;
			}
			$studio = "family";
		}

		#$model->scenario = 'perfect';

		if($user_role == Yii::$app->params['teacherRole']) {
			$role = Yii::$app->authManager->getRolesByUser($model->id);

	        $model->role =  current($role)->name;
	    }
	
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        Tencat::UpdateName(Yii::$app->request->post('user_role').$id,$model->name);

        if($model->image){
         	Tencat::CreateImage(Yii::$app->request->post('user_role').$id,Family::getPic($studio,$model['image']));	
         }else{
         	Tencat::CreateImage(Yii::$app->request->post('user_role').$id,"http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c");	
         }

		 MailList::BatchUpdate($this->studio_id);

	    if($model->save()) {
	        $_GET['message'] = Yii::t('api', 'Update Success');

			if($user_role == Yii::$app->params['studentRole']){
		        $user =  SimpleUser::find()
                   ->select(['admin_id'=>'id','phone_number','studio_id','name','campus_id','class_id','usersig','gender','contact_phone','national_id','province','grade','school_name','graduation_at','total_score','image','token_value','is_image','vip_time'])
                   ->where(['id'=>$model->id])
                   ->one();

                $user->studio_name = Studio::findOne($model->studio_id)->name;
    			
    			return $user;
			}elseif($user_role == Yii::$app->params['teacherRole']){
				$model->studio_name = Studio::findOne(Admin::findOne($model->id)->studio_id)->name;
				return $model;
			}else{
				$model->studio_name = Studio::findOne($model->studio_id)->name;
				return $model;	
			}
	    }else {
           return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
	}

	//获取省份
	public function actionGetProvince() {
		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
		return City::find()
					 ->select(['name','province_id'=>'id'])
					 ->where(['pid'=>0])
					 ->asArray()
					 ->all();

	}

    public  function actionUpload() {

    	if(Yii::$app->request->post('user_role') == 'teacher'){

    		$studio = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id;

        }else if(Yii::$app->request->post('user_role') == 'student') {
        	$studio = User::findOne(Yii::$app->request->post('admin_id'))->studio_id;

        }else if(Yii::$app->request->post('user_role') == 'family') {
        	$studio = 'family';
        }
        $_GET['message'] = "图片上传成功";

        return $image = Upload::pic_upload($_FILES, $studio, 'picture', 'image')['image'];
    }

    //上传群图片
    public  function actionUploadToQun() {

    	// if($this->user_type == 'teacher'){
    	// 	$studio =ActivationCode::findOne(['type'=>1,'relation_id'=>$this->user_id])->studio_id;
     //    }else if($this->user_type == 'student') {
     //    	$studio = User::findOne($this->user_id)->studio_id;
     //    }else if($this->user_type == 'family') {
     //    	$studio = 'family';
     //    }
        $_GET['message'] = "图片上传成功";
        
        $size = Yii::$app->params['oss']['Size']['320x320'];

        return  Oss::getUrl1('q',Upload::pic_upload1($_FILES, 'q')['image']).$size;
    }

	//更改信息
	public function actionModify() {


		$id = $this->user_id;

		$user_role = $this->user_type;

    	if($this->user_type == 'teacher'){
        	$studio = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id;
        }else if($this->user_type == 'student') {

        	$studio =User::findOne(Yii::$app->request->post('admin_id'))->studio_id;

        }else if($this->user_type == 'family') {
        	$studio = 'family';
        }
        $load = array(Yii::$app->request->post('update_key')=>Yii::$app->request->post('update_value'));

		if(Yii::$app->request->post('update_key') == 'pic_url') {
			
			MailList::BatchUpdate($this->studio_id);

			$load = array('image'=>Yii::$app->request->post('update_value'));
		}

		if(Yii::$app->request->post('update_key') == 'name') {

			MailList::BatchUpdate($this->studio_id);

	       Tencat::UpdateName($this->user_type.$id,Yii::$app->request->post('update_value'));
		}

		if(Yii::$app->request->post('update_key') == 'gender' || Yii::$app->request->post('update_key') == 'grade') {
			$load = array(Yii::$app->request->post('update_key')=>(int)Yii::$app->request->post('update_value'));
		}
		
		if(Yii::$app->request->post('update_key') == 'bind_code') {

			$BindCode  = strtoupper(Yii::$app->request->post('update_value'));

			$studio_id = Studio::findOne(['bind_code'=>$BindCode])->id;

			if(!$studio_id) {

				return SendMessage::sendErrorMsg('画室码错误!');
			}

			$load = array('studio_id'=>$studio_id);
		}

		if(Yii::$app->request->post('bind_code')) {

			$BindCode  = strtoupper(Yii::$app->request->post('bind_code'));

			$studio_id = Studio::findOne(['bind_code'=>$BindCode])->id;

			if(!$studio_id) {

				return SendMessage::sendErrorMsg('画室码错误!');
			}

			$load = array('studio_id'=>$studio_id);
		}

		if($user_role == Yii::$app->params['teacherRole']) {

			$model = Admin::findOne($id);

			$model->load($load, '');

			$role = Yii::$app->authManager->getRolesByUser($model->id);

	        $model->role =  current($role)->name;

		}else if($user_role == Yii::$app->params['studentRole']){

			$model = SimpleUser::findOne($id);

		 	if(Yii::$app->request->post('bind_code') || Yii::$app->request->post('update_key') == 'bind_code') {
		 		$model->studio_id = $studio_id;
		 	}else{
				$model->load($load, '');
			}

		}else if($user_role == Yii::$app->params['familyRole']) {

			$model = Family::findOne($id);
			$model->load($load, '');
		}

		if(Yii::$app->request->post('update_key') == 'national_id') {
			 $model->scenario = 'student_info';
		}

		if(Yii::$app->request->post('update_key') == 'phone_number' | Yii::$app->request->post('update_key') == 'name') {
			 $model->scenario = 'modify';
		}
		
		if($model){
	        $code          =  new Code();
	        if(Yii::$app->request->post('update_key') == 'pic_url') {
				if($user_role == Yii::$app->params['studentRole']){

					if($model->is_image == 1 && $model->studio_id){
						$model->is_image = NULL;
					}
				}
			}
			if($model->save()) {
				$_GET['message'] = Yii::t('teacher','Update Success');	
				if(Yii::$app->request->post('update_key') == 'pic_url') {
					if($user_role == Yii::$app->params['studentRole']){
						$studio = ($model->is_image == 1)? 'student' : $studio;
			        	Tencat::CreateImage(Yii::$app->request->post('user_role').$id,Family::getPic($studio,$model['image']));
			        }else{
			        	Tencat::CreateImage(Yii::$app->request->post('user_role').$id,Family::getPic($studio,$model['image']));	
			        }
				}
				if($user_role == Yii::$app->params['studentRole']){
			        $user =  SimpleUser::find()
                       ->select(['admin_id'=>'id','phone_number','studio_id','name','campus_id','class_id','usersig','gender','contact_phone','national_id','province','grade','school_name','graduation_at','total_score','image','token_value','is_image','vip_time'])
                       ->where(['id'=>$id])
                       ->one();

                    $user->studio_name = Studio::findOne($model->studio_id)->name;
                    return $user;
				}elseif($user_role == Yii::$app->params['teacherRole']){
					$model->studio_name = Studio::findOne($this->studio_id)->name;
					return $model;
				}else{
					$model->studio_name = Studio::findOne($model->studio_id)->name;
					return $model;	
				}
			}else{
				return SendMessage::sendErrorMsg(current($model->getErrors())[0]);
			}
		}else{
			return SendMessage::sendErrorMsg(Yii::t('api', 'User Not Exist'));
		}
	}

	//家长绑定学生
	public function actionBandStudent() {

		$family_id = Yii::$app->request->post('admin_id');

		$family = Family::findOne($family_id);

		$relation  = strtoupper(Yii::$app->request->post('relation'));

		if(strlen($relation) == 8){
			$where = array('codes2.code'=>$relation,'codes2.type'=>2);
		}else if(strlen($relation) == 11){
			$where = array('user.phone_number'=>$relation,'user.studio_id'=>$family->studio_id);
		}else{
			return SendMessage::sendErrorMsg("手机号或激活码输入错误");
		}
		$student =  SimpleUser::find()
						   ->select('user.id,user.campus_id,user.studio_id')
						   ->joinWith('codes2')
						   ->where($where)
						   ->andWhere(['user.status'=>SimpleUser::STATUS_ACTIVE])
						   ->one();

		if(!$student->codes2->code) {
			return SendMessage::sendErrorMsg("不能绑定散户学生");
		}
		if(!$student){
			return SendMessage::sendErrorMsg(Yii::t('api', 'User Not Exist'));
		}
		$student_id =  $student['id'];

		$campus_id  =  $student['campus_id'];

		$family->relation_id = $student_id;
		//美术世界家长绑定
		$family->studio_id   = $student->studio_id;

		$family->campus_id   = $campus_id;

		if($family->save()) {
			$_GET['message'] = "绑定成功";
			return Family::findOne($family_id);
		}else{
			return SendMessage::sendVerifyErrorMsg($family, Yii::t('api', 'Verify Error'));
		}
	}
	//获取用户信息

	public function actionGetInfo($admin_id,$user_role='') {
		$_GET['message'] = "获取信息成功";

		$user_role = $this->user_type;
		#return $user_role;
		switch ($user_role) {
			case 'teacher':
				$model = Admin::findOne($admin_id);
				$model->studio_name = $this->studio_name;
				return $model;
				break;
			case 'student':
            	$model =  SimpleUser::find()
                               ->select(['admin_id'=>'id','phone_number','name','campus_id','class_id','usersig','gender','contact_phone','studio_id','national_id','province','grade','school_name','graduation_at','total_score','token_value','image','vip_time'])
                               ->where(['id'=>$admin_id])
                               ->one();
                $model->studio_name = $this->studio_name;
                return $model;
				break;
			case 'family':
				$model  = Family::findOne($admin_id);
				$model->studio_name = $this->studio_name;
				return $model;
				break;			
		}

	}

	//绑定激活码
	public function actionBandCode() {

		$code_number = strtoupper(Yii::$app->request->post('code_number'));

		$admin_id    = Yii::$app->request->post('admin_id');

		$connect = Yii::$app->db->beginTransaction();

		if($this->user_role == 10){
			$user = Admin::findOne($admin_id);

			$code_type = 1;

		}elseif($this->user_role == 20){

			$user = User::findOne($admin_id);

			$code_type = 2;

		}

		$code = ActivationCode::findOne(
					[
					 'code'      =>  $code_number,
					 'status'    =>  ActivationCode::STATUS_ACTIVE,
					 'studio_id' =>  $this->studio_id,
					 'type'      =>  $code_type
					]
				);


		if(!$code) {
			return SendMessage::sendErrorMsg('激活码不存在');
		}

		if(ActivationCode::findOne(['relation_id'=>$admin_id,'studio_id'=>$this->studio_id,'type'=>$code_type,'status'=>ActivationCode::STATUS_ACTIVE])) {

			return SendMessage::sendErrorMsg('用户已经绑定了激活码');
		}

		if($code->relation_id){
			if($code->is_first != 0 && $code->type == $code_type) {
				return SendMessage::sendErrorMsg('激活码已经绑定,请联系老师');
			}
		}

		try{

			if($this->user_role == 20) {

				$user->is_review  = User::REVIEW_ED;

			}elseif($this->user_role == 10) {

				$code_admin   = Admin::findOne($code->relation_id);
				
				$user->role   = $code_admin->auths->item_name;

			}

			if($code->relation_id) {
				if($code->relation_id != $admin_id){
					if($code_type == 1){
						$old = Admin::findOne($code->relation_id);

						$user->is_create  = $old->is_create;
						
					}elseif($code_type == 2){
						$old = User::findOne($code->relation_id);
					}
					if(!$old->delete()) {
					   throw new ErrorException(Errors::getInfo($old->getErrors()));	
					}
				}
			}
			$user->campus_id   =  $code->campus_id;

			$user->class_id    =  $code->class_id;

			$user->vip_time    = date("Y-m-d",NewActivationCode::getEndTime(time(),$code->activetime));

			if($code->is_first == 0) {

				$code->due_time = date("Y-m-d",NewActivationCode::getEndTime(time(),$code->activetime));
			}

			$code->relation_id = $admin_id;

			$code->is_active = ActivationCode::USE_ACTIVE;

			$code->is_first += 1;

			if(!$code->save()) {
				throw new ErrorException(Errors::getInfo($code->getErrors()));	
			}

			if(!$user->save()) {
				throw new ErrorException(Errors::getInfo($user->getErrors()));	
			}
			$connect->commit();

			$_GET['message'] = '绑定成功';
			
			if($code_type == 2){

            	$user =  SimpleUser::find()
	                               ->select(['admin_id'=>'id',
	                               			 'phone_number',
	                               			 'studio_id','name',
	                               			 'campus_id',
	                               			 'class_id',
	                               			 'usersig',
	                               			 'gender',
	                               			 'contact_phone',
	                               			 'national_id',
	                               			 'province',
	                               			 'grade',
	                               			 'school_name',
	                               			 'graduation_at',
	                               			 'total_score',
	                               			 'image',
	                               			 'token_value',
	                               			 'is_image',
	                               			 'vip_time'
	                               			])
	                               ->where(['id'=>$admin_id])
	                               ->one();
            }

            $studio_name = Studio::findOne($user->studio_id)->name; 

            $user->studio_name = $studio_name;
            return $user;
		} catch (ErrorException $e) {
		    $connect->rollBack();
		    return SendMessage::sendErrorMsg($e->getMessage());
		}

	}

    //发送手机验证码
    public function actionSendPhoneVerifyCode($phone_number,$msg_time,$msg_code,$isArtWorld = 0)
    {	

    	if(!Format::ReturnDuanXin($msg_time,$msg_code)) {

    		return SendMessage::sendErrorMsg("短信发送失败!");
    	}

        $alidayu = new Alidayu();
        
        $name = ($isArtWorld == 0) ? "云校美术" : "美术世界APP";

        $res = $alidayu->sendPhoneVerifyCode($phone_number,$name);

        if($res != false){
            return SendMessage::sendSuccessMsg(Yii::t('api', 'Verify Code Send Success'));
        }else{
            return SendMessage::sendErrorMsg("短信发送失败,请您更换时间段重新发送.");
        }
    }

	//获取用户详情
	public function actionGetUserInfo() {

		$user_role = Yii::$app->request->post('user_role');

		$id  = Yii::$app->request->post('admin_id');

		if(!$user_role) {
			return SendMessage::sendErrorMsg("user_role不能为空!");
		}
		if(!$id) {
			return SendMessage::sendErrorMsg("admin_id不能为空!");
		}

		$_GET['message'] = "获取信息成功";
		switch ($user_role) {
			case 'teacher':
				return Admin::findOne($id);
				break;
			case 'student':
				return SimpleUser::find()
                       ->select(['admin_id'=>'id','phone_number','studio_id','name','campus_id','class_id','usersig','gender','contact_phone','national_id','province','grade','school_name','graduation_at','total_score','image','token_value','is_image'])
                       ->where(['id'=>$id])
                       ->one();
				break;
			case 'family':
				return Family::findOne($id);
				break;	
		}

	}

}
?>