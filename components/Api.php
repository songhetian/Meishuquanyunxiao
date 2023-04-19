<?php 	

namespace components;
use \common\models\Studio;
use \common\models\Format;
use \common\models\ActivationCode;
use yii;

class Api {
	static public function deleteValidation($type = NULL, $token_value = NULL)
	{
		if($token_value && $type){
        	switch ($type) {
				case 'student':
					$account = \common\models\User::findOne(['token_value' => $token_value]);
					$code        = ActivationCode::findOne(['relation_id'=>$account->id,'type'=>2]);
					if($code){
						$due_time = $code->due_time;
					}
					break;
				case 'teacher':
					$account  = \backend\models\Admin::findOne(['token_value' => $token_value]);
					$code     = ActivationCode::findOne(['relation_id'=>$account->id,'type'=>1]);
					if($code){
						$due_time = $code->due_time;
					}
					break;
				case 'family':
					$account = \common\models\Family::findOne(['token_value' => $token_value]);
					break;
			}
			if(!$account) {
				return false;
			}

			if($type != 'family') {
				if($code) {
					if($code->status = 0) {
						return false;
					}
				}
			}

			if($type != 'family') {
				if($code){
					if(Format::EndTime($due_time) <= 0 && $code->is_first != 1){
						return false;
					}
				}
			}

			if($account->status == 0){
				return false;
			}

        }
        return true;
	}
	static public function getUser($type = NULL, $token_value = NULL)
	{
		if($token_value && $type){
        	switch ($type) {
				case 'student':
					$account = \common\models\User::findOne(['token_value' => $token_value]);
					break;
				case 'teacher':
					$account = \backend\models\Admin::findOne(['token_value' => $token_value]);
					break;
				case 'family':
					$account = \common\models\Family::findOne(['token_value' => $token_value]);
					break;
			}

			return $account->id ? $account->id : false;

        }
	}
	static public function getStudioType($type = NULL, $token_value = NULL)
	{
		if($token_value && $type){
        	switch ($type) {
				case 'student':
					$studio_id = \common\models\User::findOne(['token_value' => $token_value])->studio_id;
					break;
				case 'teacher':
					$studio_id = \backend\models\Admin::findOne(['token_value' => $token_value])->studio_id;
					break;
				case 'family':
					$studio_id = \common\models\Family::findOne(['token_value' => $token_value])->studio_id;
					break;
			}
			return Studio::findOne($studio_id)->type;

        }
	}
	static public function getStudioName($type = NULL, $token_value = NULL)
	{
		if($token_value && $type){
        	switch ($type) {
				case 'student':
					$studio_id = \common\models\User::findOne(['token_value' => $token_value])->studio_id;
					break;
				case 'teacher':
					$studio_id = \backend\models\Admin::findOne(['token_value' => $token_value])->studio_id;
					break;
					break;
				case 'family':
					$studio_id = \common\models\Family::findOne(['token_value' => $token_value])->studio_id;
					break;
			}
			return Studio::findOne($studio_id)->name;

        }
	}	
	static public function getStudioID($type = NULL, $token_value = NULL)
	{
		if($token_value && $type){
        	switch ($type) {
				case 'student':
					$studio_id = \common\models\User::findOne(['token_value' => $token_value])->studio_id;
					break;
				case 'teacher':
					$studio_id = \backend\models\Admin::findOne(['token_value' => $token_value])->studio_id;
					break;
				case 'family':
					$studio_id = \common\models\Family::findOne(['token_value' => $token_value])->studio_id;
					break;
			}
			return $studio_id;

        }
	}	
}
?>