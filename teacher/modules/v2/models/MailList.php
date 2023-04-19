<?php

namespace teacher\modules\v2\models;

class MailList extends \common\models\MailList
{	
    public function fields()
	{
	    $fields = parent::fields();
	    return $fields;
	}
	//创建
	public static function Add($user_id,$role,$studio_id) {
		$MailList = new self();
		$MailList->user_id = $user_id;
		$MailList->role = $role;
		$MailList->studio_id = $studio_id;

		if($MailList->save()){
			return $MailList;
		}else{
			return false;
		}	
	}

	//批量修改
	public static function BatchUpdate($studio_id) {

		return self::updateAllCounters(['new'=>1],['studio_id'=>$studio_id]);
	}

	//变更旧值
	public static function UpdateOld($user_id,$role) {

		$mail = self::findOne(['user_id'=>$user_id,'role'=>$role]);

		$mail->old = $mail->new;

		if($mail->save()) {
			return true;
		}else{
			return false;
		}
	}
}
