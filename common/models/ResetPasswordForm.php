<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $id;
    public $password;
    public $password_hash;
    public $verify_password;

    private $_user;

    public function rules()
    {
        return [
            //修改密码
            [['id', 'password', 'password_hash', 'verify_password'], 'required'],
            [['password', 'password_hash', 'verify_password'],'string', 'min' => 6],
            ['password', 'validatePassword'],
            ['password_hash', 'compare', 'compareAttribute' => 'password', 'operator' => '!=', 'message' => '新密码不能与原密码相同'],
            ['verify_password', 'compare', 'compareAttribute' => 'password_hash', 'message' => '两次输入的新密码必须一致'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'password' => '原密码',
            'password_hash' => '新密码',
            'verify_password' => '确认新密码'
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
            if ($user) {
                if(!$user->validatePassword($this->password)){
                    $this->addError($attribute, '密码不正确');
                }
            }
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
            $this->_user = User::findIdentity($this->id); 
        }
        if(!$this->_user){
            $this->addError('id', '用户不存在');
        }
        return $this->_user;
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        if ($this->validate()) {
            $user = $this->_user;
            $user->password_hash = $this->password_hash;
            return $user->save();
        }
    }
}
