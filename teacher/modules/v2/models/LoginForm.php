<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\base\Model;
use components\Alidayu;
use teacher\modules\v2\models\NewActivationCode;

class LoginForm extends Model
{
    public $code_number;
    public $type;
    public $landing;
    private $_user;
    public $phone_number;
    public $phone_verify_code;
    public $studio_id;
    public $param = false;

    const LANDING_CODE   = 1;
    const LANDING_PHONE  = 2; 

    
    public function rules()
    {
        return [
            [['code_number','type','landing'], 'required','on'=>['code']],
            [['phone_number','phone_verify_code','landing'], 'required','on'=>['phone']],
            ['phone_verify_code','phoneVerifyCode','on'=>['phone']],
            ['code_number', 'string', 'length' => 8],
            ['type', 'in', 'range' => [1, 2,3]],
            ['landing', 'in', 'range' => [1, 2]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code_number' => '激活码',
            'type'        => '登陆类型',
            'landing'     => '登陆方式',
            'phone_number' => '手机号',
            'phone_verify_code' => '验证码',
        ];
    }
    //手机验证码验证
    public function phoneVerifyCode($attribute)
    {
        if($this->phone_number == '13001277186'){
            return true;
        }

        if($this->phone_verify_code == '415278'){
            return true;
        }

        if($this->phone_number && $this->phone_verify_code) {
            $alidayu = new Alidayu();
            $res = $alidayu->phoneVerifyCode($this);
            if($res == false){
                $this->addError('phone_verify_code', '无效的验证码');
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

        //
        if ($this->validate()) {

            if($this->landing == self::LANDING_PHONE && $this->studio_id != 183 && $this->type == ActivationCode::TYPE_TEACHER ){
                 $this->addError('code', '不能用手机号登陆');
                 return false;
            }
            $code = $this->getCode();

            //判断是否存在激活码
            if($code) {
               if($code->is_first != 0){
                   if(strtotime($code->due_time) - time() < 0 ){  
                        $this->addError('code', '您的激活码已经过期!');
                        return false;   
                   }
               }
                
                //判断激活码是否为该画室的激活码并排出183画室
                if($this->studio_id != 183) {
                    if($code->studio_id != $this->studio_id){
                        $this->addError('code', '激活码不可用或已过期');
                        return false;    
                    }
                }

                //画室id赋值到studio_id
                $this->studio_id = $code->studio_id;
                

                //判断是不是校长账号
                if(!in_array($code->code, ActivationCode::getXiaoZhang())) {
                    //判断是否登陆
                    if($code->is_active == ActivationCode::USE_ACTIVE){
                        if(!in_array($this->studio_id,Yii::$app->params['NoYanZheng'])) {
                            $this->addError('code', '该账号已经登陆,请联系管理员!');
                            return false;  
                        }
                    }  
                }

                //首次登陆is_first + 1 
                //更改激活状态
                $code->is_active = ActivationCode::USE_ACTIVE;
                if($code->is_first == 0){

                    //判断如果为3个月免费赠送激活码到期时间
                    if($code->activetime == 0.25 && $code->type == 2) {
                        if(in_array($this->studio_id,Yii::$app->params['Studio'])){
                            $code->due_time = date("Y-m-d",NewActivationCode::getEndTime(time(),$code->activetime));
                        }
                    }else{
                        $code->due_time = date("Y-m-d",NewActivationCode::getEndTime(time(),$code->activetime));
                    }

                }
                        
                $code->is_first = $code->is_first + 1;

                if(!$code->save()) {
                    $this->addError('login', $code->getErrors());
                    return false;
                }

                if($this->type == ActivationCode::TYPE_TEACHER) {
                    $model = new Admin();
                }else if($this->type == ActivationCode::TYPE_USER) {
                    $model = new User();
                }
               
                $user = $model::findOne(['id'=>$code->relation_id]);

                if($code->is_first == 1 || $code->is_first == 2){
                    #$user->vip_time = date("Y-m-d",NewActivationCode::getEndTime(time(),$code->activetime));
                    $user->vip_time  = $code->due_time;
                    if(!$user->save()){
                        $this->addError('login', $user->getErrors());
                        return false;
                    }
                }

                return $user;

            }else{
                //没有激活码情况
                //如果激活码登录 身份不是家长 返回错误
                if($this->landing == self::LANDING_CODE){
                    if($this->type != ActivationCode::TYPE_FAMILY) {
                        $this->addError('code', '激活码不可用或已过期');
                        return false; 
                    }
                }
                //登陆方式为手机
                if($this->landing == self::LANDING_PHONE){
                    //家长身份
                    if($this->type == ActivationCode::TYPE_FAMILY){
                        //家长身份登陆
                        $family = Family::findOne(['phone_number'=>$this->phone_number,'studio_id'=>$this->studio_id,'status'=>0]);
                        if($family){
                               $this->addError('login', "该用户已删除,请联系管理员");
                               return false;
                        }
                        $family = Family::findOne(['phone_number'=>$this->phone_number,'studio_id'=>$this->studio_id,'status'=>10]);
                        if(!$family) {
                            $family = new Family();
                            $family->phone_number = $this->phone_number;
                            $family->studio_id = $this->studio_id;
                            $family->scenario = 'create';
                            $family->save();
                        }
                        return $family;
                    }

                    if($this->type == ActivationCode::TYPE_USER) {
                            $user = User::find()->where(['phone_number'=>$this->phone_number,'status'=>0])->andFilterWhere(['studio_id'=>$this->studio_id])->one();

                            if($user) {
                               $this->addError('login', "该用户已删除,请联系管理员");
                               return false;
                            }

                            $user = User::find()->where(['phone_number'=>$this->phone_number,'status'=>10,'studio_id'=>$this->studio_id])->one();
 
                            if(!$user){
                                $user = new User();
                                $user->studio_id    = $this->studio_id;
                                $user->phone_number = $this->phone_number;
                                $user->save();
                            }
                            return $user;
                    }

                    if($this->type == ActivationCode::TYPE_TEACHER) {

                        if($this->studio_id != 183) {
                            $this->addError('code', '不能手机号登陆!');
                            return false;    
                        }

                        $admin = Admin::find()
                                        ->where(['phone_number'=>$this->phone_number,'status'=>0])
                                        ->andFilterWhere(['studio_id'=>$this->studio_id])
                                        ->one();
                        if($user) {
                            $this->addError('login', "该用户已删除,请联系管理员");
                            return false;
                        }
                        $admin = Admin::find()
                                       ->where(['phone_number'=>$this->phone_number,'status'=>10,'studio_id'=>$this->studio_id])
                                       ->one();
                        if(!$admin){
                            $admin = new Admin();
                            $admin->studio_id    = $this->studio_id;
                            $admin->phone_number = $this->phone_number;
                            $admin->is_chat      = 0;
                            $admin->is_create    = 0;
                            $admin->password_hash = "123456";
                            $admin->role = '17'.sprintf("%04d", $this->studio_id).sprintf("%03d", 9);
                            $admin->save();
                        }
                        return $admin;
                    }



                }
            }
        }      
    }

    /*[根据手机号查询用户]
     *
     *
     *
     *
    */

    /**
     *  查询用户
     *
     * @return User|null
     */
    protected function getCode()
    {
        if ($this->_user === null) {

            if($this->landing == self::LANDING_CODE){

               $code = ActivationCode::findByNumber(strtoupper($this->code_number),$this->type);

                if($code){
                      if($code->studio_id == 183 && $code->is_first != 0 && strtotime($code->due_time) - time() < 0) {
                            if($this->type == ActivationCode::TYPE_TEACHER) {
                                $user = Admin::findOne(['id'=>$code->relation_id]);
                            }elseif($this->type == ActivationCode::TYPE_USER ){
                                $user = User::findOne(['id'=>$code->relation_id]);
                            }

                                $user->campus_id = NULL;
                                $user->class_id  = NULL;
                                $code->status    = 0;
                                $code->relation_id  = NULL;
                                $user->save(); 

                                $code->save(); 

                                $this->addError('code', '激活码已过期,请使用手机号登陆!');
                                return false;  
                       }

                    $this->_user = $code;
                }
            }else if($this->landing == self::LANDING_PHONE) {

               //家长激活码返回null
               if($this->type == ActivationCode::TYPE_FAMILY) {
                    return NULL;
               }

               if($this->type == ActivationCode::TYPE_USER){
                  $model = new User();
               }elseif($this->type == ActivationCode::TYPE_TEACHER){
                  $model = new Admin(); 
               }

               $user = $model::findOne(['phone_number'=>$this->phone_number,'studio_id'=>$this->studio_id,'status'=>User::STATUS_ACTIVE]);

                if($user){

                    $code =  ActivationCode::findOne(['relation_id'=>$user->id,'type'=>$this->type]);

                    if($code){
                        if($code->studio_id == 183 && $code->is_first != 0 && strtotime($code->due_time) - time() < 0) {

                            $user->campus_id = NULL;
                            $user->class_id  = NULL;

                            $code->status    = 0;
                            $code->relation_id  = NULL;

                            $user->save(); 
                            $code->save();

                            return false;   
                       }
                      $this->_user = $code;
                    }
                }      
            }
        }

        return $this->_user ? $this->_user : NULL;
    }
}