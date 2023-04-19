<?php

namespace teacher\modules\v2\models;

use yii\base\Model;

class Common extends Model
{
	public static function getAccount($user_role, $account_id)
    {
    	switch ($user_role) {
			case 'student':
				$account = User::findOne($account_id);
				break;
			case 'teacher':
				$account = Admin::findOne($account_id);
				break;
			case 'family':
				$account = Family::findOne($account_id);
				break;
		}
		return $account;
    }
}