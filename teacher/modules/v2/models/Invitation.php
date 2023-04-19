<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\base\ErrorException;
use teacher\modules\v2\models\Tencat;


class Invitation extends \common\models\Invitation
{	
	public $tid;
	//生成邀请记录
	public static function CreateOne($id,$role,$invitee_id,$invitee_role) {

		$model = new self();

		if(self::findOne(['invitee_id'=>$invitee_id,'invitee_role'=>$invitee_role])) {
			return true;
		}

		$model->invite_id    = $id;

		$model->role         = $role;

		$model->invitee_id   = $invitee_id;

		$model->invitee_role  = $invitee_role;

		$model->give_days     = 7;

		return $model->save() ? true : false;
	}

	//双方增加天数

	public static function AddDays($id) {

		$model = self::findOne($id);
		
 
		$lan  = "您的好友已邀请成功，特为您呈上免费7天会员。邀请不停，赠送不停！";

		$bei  = "恭喜您注册成功，特为您呈上免费7天会员。";

		//邀请人
		switch ($model->role) {
			case 10:
				$invite = Admin::findOne($model->invite_id);

				$code = ActivationCode::findOne(['type'=>1,'relation_id'=>$model->invite_id,'status'=>10,'studio_id'=>183]);


				$account[] = 'teacher'.$invite->id;

				break;
			case 20:
				$invite = User::findOne($model->invite_id);

				$code = ActivationCode::findOne(['type'=>2,'relation_id'=>$model->invite_id,'status'=>10,'studio_id'=>183]);

				$account[] = 'student'.$invite->id;
				break;
			case 30:
				$invite = Family::findOne($model->invite_id);

				$account[] = 'family'.$invite->id;
				break;
			default:
				# code...
				break;
		}
		
		//被邀请人
		switch ($model->invitee_role) {
			case 10:
				$invitee = Admin::findOne($model->invitee_id);
				$account1[] = 'teacher'.$invitee->id;
				#$invitee_code = ActivationCode::findOne(['type'=>1,'relation_id'=>$relation_id,'status'=>10,'studio_id'=>183]);

				break;
			case 20:
				$invitee = User::findOne($model->invitee_id);

				$account1[] = 'student'.$invitee->id;
				#$invitee_code = ActivationCode::findOne(['type'=>2,'relation_id'=>$relation_id,'status'=>10,'studio_id'=>183]);

				break;
			case 30:
				$invitee = Family::findOne($model->invitee_id);
				$account1[] = 'family'.$invitee->id;
				break;
			default:
				# code...
				break;
		}


		$days = $model->give_days;

		if($code) {
			$code->due_time = self::SetDays($code->due_time,$days);
		}

		$invite->vip_time  = self::SetDays($invite->vip_time,$days);

		$invitee->vip_time = self::SetDays($invitee->vip_time,$days);

		$model->status = 0;
		//事务
		$connect = Yii::$app->db->beginTransaction();


		try{

			if($code){
				if(!$code->save()) {	
					throw new ErrorException(Errors::getInfo($code->getErrors()));	
				}
			}

			if(!$invite->save()) {
				throw new ErrorException(Errors::getInfo($invite->getErrors()));
			}
			if(!$invitee->save()) {
				throw new ErrorException(Errors::getInfo($invitee->getErrors()));
			}
			if(!$model->save()) {
				throw new ErrorException(Errors::getInfo($model->getErrors()));
			}

			$connect->commit();

			$flag = true;
		} catch (ErrorException $e) {
		    $connect->rollBack();
		    $flag = false;
		}

		if($flag) {
			Tencat::SendMsg($account,$lan);
			Tencat::SendMsg($account1,$bei);
		}

		return $flag;
	}

	public static function SetDays($vip_time,$days) {

		$vip_time = $vip_time ? $vip_time : date("Y-m-d",time());

		if(strtotime(date("Y-m-d",time())) >= strtotime($vip_time)) {

			$vip_time = date("Y-m-d",time());
		}

		return date("Y-m-d",strtotime("+$days day",strtotime($vip_time)));
	}

}
