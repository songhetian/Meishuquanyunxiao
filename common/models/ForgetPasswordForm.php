<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;
use components\Alidayu;

class ForgetPasswordForm extends Model
{
    public $phone_number;
    public $phone_verify_code;
    public $password_hash;
    public $studio_id;
    private $_user;


    public function rules()
    {
        return [
            [['phone_number', 'phone_verify_code', 'password_hash'], 'required'],
            ['phone_verify_code','phoneVerifyCode'],
            [['password_hash'],'string', 'min' => 6],
        ];
    }

    public function attributeLabels()
    {
        return [
            'phone_number' => '手机号',
            'phone_verify_code' => '验证码',
            'password_hash' => '密码',
        ];
    }

    //手机验证码验证
    public function phoneVerifyCode($attribute)
    {
        if($this->phone_number && $this->phone_verify_code) {
            $alidayu = new Alidayu();
            $res = $alidayu->phoneVerifyCode($this);
            if($res == false){
                $this->addError('phone_verify_code', '无效的验证码');
            }
        }
    }

    /**
     * 使用提供的用户用户名和密码登录
     *
     * @return boolean 用户是否登录成功
     */
    public function forget($studio_id)
    {
        $this->studio_id = $studio_id;
        if ($this->validate()) {
            if($user = $this->getUser($studio_id)){
                $user->password_hash = $this->password_hash;
                if($user->save()){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 查询用户
     *
     * @return User|null
     */
    protected function getUser()
    {
        $this->_user = User::findByPhoneNumber($this->phone_number,$this->studio_id);
        if(!$this->_user){
            $this->addError('phone_number', '手机号不存在');
        }
        return $this->_user;
    }
}