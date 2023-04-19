<?php 

namespace teacher\modules\v1\models;

use teacher\modules\v1\models\Admin;

class LoginForm extends \backend\models\LoginForm
{
    private $_user;
/**
     * 登录用户使用提供的用户名和密码
     *
     * @return boolean 用户是否登录成功
     */
    public function login()
    {
        if ($this->validate()) {
            return $this->getAdmin();
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
        if ($this->_user === null) {
            $this->_user = Admin::findByPhoneNumber($this->phone_number);
        }
        return $this->_user;
    }
}

?>