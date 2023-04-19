<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;

class LoginForm extends Model
{
    public $account;
    public $password_hash;
    public $studio_id;
    private $_user;
    // public $studio_id;

    public function rules()
    {
        return [
            [['account', 'password_hash'], 'required'],
            ['password_hash', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'account' => '学号/手机号',
            'password_hash' => '密码',
        ];
    }
    /**
     * 验证密码
     *
     * @param string $attribute 目前验证的属性
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user && !$user->validatePassword($this->password_hash)) {
                $this->addError($attribute, '密码不正确');
            }
        }
    }

    /**
     * 使用提供的用户用户名和密码登录
     *
     * @return boolean 用户是否登录成功
     */
    public function login($studio_id)
    {   
        $this->studio_id = $studio_id;
        if ($this->validate()) {
            return $this->getUser();
        } else {
            return false;
        }
    }

    /**
     * 查询用户
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            if(preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/', $this->account)){  
                $this->_user = User::findByPhoneNumber($this->account,$this->studio_id); 
            }else{
                $this->_user = User::findByStudentId($this->account,$this->studio_id);
            }
        }
        if(!$this->_user){
            $this->addError('account', '用户名不存在');
        }
        return $this->_user;
    }
}