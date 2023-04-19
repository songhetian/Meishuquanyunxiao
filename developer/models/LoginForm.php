<?php

namespace developer\models;

use Yii;
use yii\base\Model;
use developer\models\Developer;

class LoginForm extends Model
{
    public $phone_number;
    public $password;

    private $_user;

    public function rules()
    {
        return [
            [['phone_number', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'phone_number' => '管理员账号',
            'password' => '管理员密码',
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
            $user = $this->getDeveloper();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '管理员账号或密码不正确');
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
            return Yii::$app->user->login($this->getDeveloper());
        } else {
            return false;
        }
    }

    /**
     *  查询用户
     *
     * @return User|null
     */
    protected function getDeveloper()
    {
        if ($this->_user === null) {
            $this->_user = Developer::findByPhoneNumber($this->phone_number);
        }
        return $this->_user;
    }
}