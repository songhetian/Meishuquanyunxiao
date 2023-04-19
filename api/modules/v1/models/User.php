<?php

namespace api\modules\v1\models;

use Yii;
use components\Jpush;
use backend\models\Admin;

class User extends \common\models\User
{

    public function fields()
	{
	    $fields = parent::fields();

        if($this->campuses){
            $fields['campus_id'] = function () {
                return [
                    'id' => $this->campuses->id,
                    'name' => $this->campuses->name
                ];
            };
        }
        
        if($this->classes){
            $fields['class_id'] = function () {
                return [
                    'id' => $this->classes->id,
                    'name' => $this->classes->name,
                    'supervisor_name' => Admin::findOne($this->classes->supervisor)->name,
                    'supervisor_phone_number' => Admin::findOne($this->classes->supervisor)->phone_number,
                ];
            };
        }
        
        if($this->race){
            $fields['race'] = function () {
                return [
                    'id' => $this->races->id,
                    'name' => $this->races->name
                ];
            };
        }
        
        if($this->provinces){
            $fields['province'] = function () {
                return [
                    'id' => $this->provinces->id,
                    'pid' => $this->provinces->pid,
                    'name' => $this->provinces->name
                ];
            };
        }
        
        if($this->citys){
            $fields['city'] = function () {
                return [
                    'id' => $this->citys->id,
                    'pid' => $this->citys->pid,
                    'name' => $this->citys->name
                ];
            };
        }
        
        if($this->unitedExamProvinces){
            $fields['united_exam_province'] = function () {
                return [
                    'id' => $this->unitedExamProvinces->id,
                    'pid' => $this->unitedExamProvinces->pid,
                    'name' => $this->unitedExamProvinces->name
                ];
            };
        }
        
	    unset(
            $fields['password_hash'],
            $fields['is_graduation'],
            $fields['graduation_at'],
            $fields['auth_key'],
            $fields['password_reset_token'],
            $fields['access_token'],
            $fields['admin_id'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['is_review'],
            $fields['status']
        );
	    return $fields;
	}

    /**
     * [compareDeviceToken 单点登录device_token比对]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     * @param     [type]        $user         [用户]
     * @param     [type]        $device_token [用户令牌]
     * @return    [type]                      [description]
     */
    static function compareDeviceToken($user, $device_token)
    {
        if($user->device_token != NULL && $device_token != $user->device_token){
            $client = new Jpush($user->campus_id);
            $client->sendPrivateNotification($user->device_token, Yii::t('api', 'Login Elsewhere') , ['code' => 1001]);
        }
        $user->device_token = $device_token;
        if($user->save()){
            return true;
        }   
    }
}
