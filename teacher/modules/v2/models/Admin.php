<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use components\Jpush;
use common\models\Format;

class Admin extends \backend\models\Admin
{
    public $studio_name;
    public function beforeSave($insert)
    {        
        if ($this->isNewRecord) {
            $this->created_at = time();
            $this->updated_at = time();
            $this->setPassword($this->password_hash);
            $this->generateAuthKey();
            $this->setUuid();
        }else{
             $this->updated_at = time();
            if($this->password_hash != $this->getOldAttribute('password_hash')){
                $this->setPassword($this->password_hash);
            }
        }
        return true;
    }

    public function rules()
    {
        return [
            //特殊需求
           # [['phone_number', 'password_hash'], 'required'],
            [['role'], 'required', 'on' => ['create','update','perfect']],
            [['campus_id'], 'required', 'on' => ['create','update']],

            [['name'],'UniqueName','on'=>['modify','perfect']],
            

            [['name'], 'required', 'on' => ['perfect']],

            [['phone_number', 'password_reset_token'], 'unique', 'on' => ['create','update','perfect']],

            [['phone_number'],'UniquePhone','on'=>'modify'],
            //字段规范
            ['phone_number', 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9]|19[0-9])[0-9]{8}$/', 'message' => '请填写有效手机号。'],
            ['password_hash', 'string', 'min' => 6],
            ['auth_key', 'default', 'value' => self::AUTH_KEY], 
            ['is_all_visible', 'default', 'value' => self::ALL_VISIBLE],
            ['is_main', 'default', 'value' => self::MAIN_NOT_YET],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['gender', 'in', 'range' => [self::GENDER_MALE, self::GENDER_FEMALE]],
            //字段类型
            [['is_all_visible', 'is_first','studio_id','is_main', 'created_at', 'updated_at', 'status','province','gender','is_chat','is_cloud','is_create'], 'integer'],
            ['is_first', 'default', 'value'  => 0],
            ['is_create_number', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['is_create_number', 'default', 'value'  => 10],
            ['is_create', 'default', 'value'  => 1],
            ['is_chat', 'default', 'value'  => 1],
            ['is_cloud', 'default', 'value' => 1], 
            ['phone_number', 'string', 'max' => 11],
            [['name', 'auth_key','expert_category','qrcode'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token','vip_time'], 'string', 'max' => 100],
            [['usersig'], 'string', 'max' => 500],
            [['image'], 'string', 'max' => 100],
            [['campus_id', 'category_id', 'class_id'], 'safe'],
            [['username'], 'safe'],

        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['admin_id'] = function() {
            return $this->id;
        };
        if($this->studio_id){
            $fields['studio_id'] = function() {
                return $this->studio_id;
            };
        }else{
            $fields['studio_id'] = function() {
                return $this->getStudio($this->id);
            };
        }
        $fields['identifier'] = function () {
            return 'teacher'.$this->id;
        };
        $fields['is_vip'] = function () {
            if(!empty($this->vip_time) && strtotime($this->vip_time) > time()){
                return true;
            }else{
                return false;
            }
        }; 
        $fields['vip_time'] = function() {
            if($this->codes){
              return  $this->codes->due_time;
            }else{
              return  $this->vip_time;
            }
        };
        $fields['pic_url'] = function () {
            $size = Yii::$app->params['oss']['Size']['320x320'];
            $studio = $this->studio_id;
            if($this->image){
                return Oss::getUrl($studio, 'picture', 'image', $this->image).$size;
            }else{
                return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
            }
        };
        $fields['expert_category'] = function () {
            return self::concatCategory($this->expert_category);
        };
        $fields['gender'] = function () {
            return self::getValues('gender',$this->gender);
        };

        if($this->province){
            $fields['province'] = function() {
                return $this->citys->name;
            };
        }
        $fields['user_role'] = function () {

            return "teacher";
        };
        $fields['campus_id'] = function () {
            return $this->concatCampus($this->campus_id);
        };
        $fields['campusId_Rn'] = function () {
            return $this->campus_id;
        };
        if($this->codes){
            $fields['code_number'] = function () {
                return $this->codes->code;
            };
        }
        if($this->studio_name) {
            $fields['studio_name'] = function () {
                return $this->studio_name;
            };   
        }
        $fields['is_create'] = function () {
            return $this->is_create ? true : false;
        };        

        $fields['is_create_class'] = function () {

            $item_name =  $this->auths->item_name;
           
            $pid = substr($item_name,-3);

            //判断是否为校长或者管理员身份
           return  in_array($pid,Yii::$app->params['Shenfen']) ? true : false;
            
        };

        if($this->codes){
            $fields['surplus_time'] = function() {

                $int =  Format::EndTime($this->codes->due_time);

                if($this->codes->is_first == 0) {

                    $activetime = $this->codes->activetime;

                    $activetime = ($activetime == 0) ? 1 : $activetime;

                    return round(365 * $activetime).'天';

                }else{

                    return ($int > 0) ? $int.'天':"已过期";
                }
            };
        };
        
        $fields['is_code_create'] = function () {
            
            $role     = Yii::$app->authManager->getRolesByUser($this->id);
            if(!$role) {
                return false;
            }
            $role_key =  key($role);
            $pid = substr($role_key,-3);
            if(!in_array($pid,Yii::$app->params['Shenfen'])) {
                return false;
            }else{
                return true;
            }
        };

        unset(
            $fields['id'],
            $fields['image'],
            $fields['class_id'],
            $fields['category_id'],
            $fields['is_all_visible'],
            $fields['is_main'],
            $fields['auth_key'],
            $fields['password_hash'],
            $fields['password_reset_token'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['is_cloud'],
            $fields['status']
        );
        return $fields;
    }
    //手机验证码验证
    public function UniquePhone($attribute)
    {
        $studio = $this->studio_id;

        $list =  Admin::find()
                     ->select('phone_number')
                     ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$studio])
                     ->andWhere(['<>','id',$this->id])
                     ->asArray()
                     ->all();
        $phone_number = array_column($list, 'phone_number');  

        if(in_array($this->phone_number, $phone_number)) {
             $this->addError('phone', "{$this->phone_number}已经被占用");
        }else{
            return true;
        }       
       
    }

    //手机验证码验证
    public function UniqueName($attribute)
    {
        $studio = $this->studio_id;


        $list =  Admin::find()
                     ->select('name')
                     ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$studio])
                     ->andWhere(['<>','id',$this->id])
                     ->asArray()
                     ->all();
        $name = array_column($list, 'name');  

        if(in_array($this->name, $name)) {
             $this->addError('name', "该昵称已经被占用");
        }else{
            return true;
        }       
       
    }
    //拼接擅长科目
    public static function concatCategory($string) {

        $list = Format::explodeValue($string);
        $name = array();
        foreach ($list as $key => $value) {
            $name[] = Category::findOne($value)->name;
        }

        return Format::implodeValue($name);
    }
    //可见校区拼接
    public static function concatCampus($string) {

        $list = Format::explodeValue($string);
        $name = array();
        foreach ($list as $key => $value) {
            $name[] = Campus::findOne($value)->name;
        }

        return Format::implodeValue($name);
    }
    /*
     *[获取课件范围]
     *
     *
     *
    */
    public static function getVisua($admin_id) {
        $list = [];
        $admin = self::findOne(['id'=>$admin_id]);
        if($admin['is_all_visible'] == self::MYSELF_VISIBLE) {
            $list =  Admin::find()->select(['admin_id'=>'id','name'])
                         ->where(['id'=>$admin_id])          
                         ->asArray()
                         ->all();
        }elseif($admin['is_all_visible'] == self::ALL_VISIBLE) {
            $campuses =  Format::concatString($admin['campus_id']);
            $list =  Admin::find()->select(['admin_id'=>'id','name'])
                         ->where(['status'=>self::STATUS_ACTIVE])
                         ->andFilterWhere(['NOT', ['name' => 'null']])       
                         ->andFilterWhere(['or like',Format::concatField('campus_id'),$campuses])
                         ->orderBy('id DESC')
                         ->asArray()
                         ->all();
        }

        return $list;
    }


    /*
     *[获取课件范围]
     *
     *
     *
    */
    public static function getVisuaForChat($admin_id) {
        $list = [];
        $admin = self::findOne(['id'=>$admin_id]);
        $campuses =  Format::concatString($admin['campus_id']);

        if($campuses){
            $list =  Admin::find()->select(['admin_id'=>'id','name'])
                         ->where(['status'=>self::STATUS_ACTIVE])
                         ->andFilterWhere(['NOT', ['name' => 'null']])       
                         ->andFilterWhere(['or like',Format::concatField('campus_id'),$campuses])
                         ->orderBy('id DESC')
                         ->asArray()
                         ->all();
        }else{
            $list =  Admin::find()->select(['admin_id'=>'id','name'])
                         ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$admin->studio_id,'is_chat'=>1])
                         ->andFilterWhere(['NOT', ['name' => 'null']])       
                         ->orderBy('id DESC')
                         ->asArray()
                         ->all();     
        }
        return $list;
    }
    //获取可见班级
    public function getClasses($admin_id,$campus_id) {
        $campuses =  Format::explodeValue($campus_id);
        $list =  Classes::find()->select('id')
                     ->where(['status'=>Classes::STATUS_ACTIVE,'campus_id'=>$campuses])
                     ->indexBy('id')
                     ->all();
        if($list) {
            $classes = array_keys($list);
            return Format::implodeValue($classes);
        }else{
            return null;
        }
    }
    //获取课件校区
    public static function GetCampus($admin_id) {
        $list   =  self::find()
                   ->where(['id'=>$admin_id])
                   ->one();
        $list = Format::explodeValue($list['campus_id']);
        $studio_id = Admin::findOne($admin_id)->studio_id;
        $campus = Campus::find()
                ->where(['studio_id'=>$studio_id,'status'=>self::STATUS_ACTIVE])
                ->all();
        return $campus;
    }
    //获取课件校区
    public  function GetCampusNames($admin_id) {

        $list   =  self::find()
                   ->where(['id'=>$admin_id])
                   ->one();
        $list = Format::explodeValue($list['campus_id']);

        $campus = Campus::find()
                ->select('name')
                ->where(['id'=>$list,'status'=>self::STATUS_ACTIVE])
                ->indexBy('name')
                ->all();
        return Format::implodeValue(array_keys($campus));

    }


    //获取取studio_id
    public function getStudio($admin_id) {
 
        $studio = self::findOne($admin_id)->studio_id;
        return $studio;
    }   
    /**
     * 根据手机号查询用户
     *
     * @param string $phone_number
     * @return static|null
     */
    public static function findByPhoneNumber($phone_number)
    {
        $qeury = static::find();
        return static::findOne(['phone_number' => $phone_number, 'status' => self::STATUS_ACTIVE]);
    }
    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->where(['type'=>1])->alias('codes');
    }
    public function getCitys()
    {
        return $this->hasOne(City::className(),['id'=>'province'])->alias('citys');
    }

    public function getAuths()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id'])->alias('auths');
    }

}