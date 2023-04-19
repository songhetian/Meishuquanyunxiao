<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;
use components\Alidayu;

/**
 * Password reset form
 */
class UpdatePhoneNumberForm extends Model
{
    public $id;
    public $phone_number;
    public $phone_verify_code;

    private $_user;

    public function rules()
    {
        return [
        	//特殊需求
        	[['id', 'phone_number', 'phone_verify_code'], 'required'],
        	//手机验证码
            ['phone_verify_code','phoneVerifyCode'],
            [['phone_number'], 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/', 'message'=>'请填写有效手机号。'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone_number' => '手机号',
            'phone_verify_code' => '验证码'
        ];
    }

    //手机验证码验证
    public function phoneVerifyCode($attribute)
    {
    	$user = $this->getUser();
        if($this->phone_number && $this->phone_verify_code) {
            $alidayu = new Alidayu();
            $res = $alidayu->phoneVerifyCode($this);
            if($res == false){
                $this->addError('phone_verify_code', '无效的验证码');
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
        }else{
        	$user = User::findOne(['phone_number' => $this->phone_number]);
	        if($user){
	        	$this->addError('phone_number', '该手机号已存在');
	        }
        }
        return $this->_user;
    }

    public function updatePhoneNumber()
    {
        if ($this->validate()) {
            $user = $this->_user;
            $user->phone_number = $this->phone_number;
           	if($user->save()){
            	return $user;
            };
        }
    }
}
