<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Studio;

class LoginForm extends Model
{
    public $phone_number;
    public $password;
    public $rememberMe = false;

    private $_user;

    public function rules()
    {
        return [
            [['phone_number', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'phone_number' => '手机号',
            'password' => '密码',
            'rememberMe' => '记住我'
        ];
    }
    
    /**
     * 验证密码
     *
     * @param string $attribute 目前验证的属性
     * @param array $params 额外的名称-值对给定的规则
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getAdmin();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '账号或密码不正确');
            }
        }
    }

    /**
     * 登录用户使用提供的用户名和密码
     *
     * @return boolean 用户是否登录成功
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getAdmin(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     *  查询用户
     *
     * @return User|null
     */
    protected function getAdmin()
    {
        if(Yii::$app->session->hasFlash('studio')){
            $studio_name =  Yii::$app->session->getFlash('studio');
            $studio_id   =  Studio::getByName($studio_name);
        }else{
            $studio_id   =  NULL;
        }

        if ($this->_user === null) {
            $this->_user = Admin::GetByPhoneNumber($this->phone_number,$studio_id);
        }
        return $this->_user;
    }
}