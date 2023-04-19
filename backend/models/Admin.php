<?php

namespace backend\models;

use Yii;
use common\models\Format;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use common\models\ActivationCode;

/**
 * This is the model class for table "{{%admin}}".
 *
 * @property integer $id
 * @property string $phone_number
 * @property string $name
 * @property integer $campus_id
 * @property integer $category_id
 * @property integer $class_id
 * @property integer $is_all_visible
 * @property integer $is_main
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Admin extends ActiveRecord implements IdentityInterface
{
    public $username;
    public $role;
    public $avatar;

    const AUTH_KEY = '134679';

    const MYSELF_VISIBLE = 0;
    const ALL_VISIBLE = 10;

    const MAIN_NOT_YET = 0;
    const MAIN_ED = 10;

    const GENDER_MALE = 10;
    const GENDER_FEMALE = 20;

    const SELL_YES = 10;
    const SELL_NO = 0;

    public static function tableName()
    {
        return '{{%admin}}';
    }
    
    public function beforeSave($insert)
    {        
        if (parent::beforeSave($insert)) {
            $this->campus_id = (is_array($this->campus_id)) ? Format::implodeValue($this->campus_id) : NULL;
            $this->category_id = (is_array($this->category_id)) ? Format::implodeValue($this->category_id) : NULL;
            $this->class_id = (is_array($this->class_id)) ? Format::implodeValue($this->class_id) : NULL;
            if ($this->isNewRecord) {
                $this->setPassword($this->password_hash);
                $this->generateAuthKey();
                $this->setUuid();
            }else{
                if($this->password_hash != $this->getOldAttribute('password_hash')){
                    $this->setPassword($this->password_hash);
                }
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($this->role){
            //关联角色
            $auth = Yii::$app->authManager;
            if($insert) {
                $role = $auth->getRole($this->role);
                $auth->assign($role, $this->id);
            } else {
                $orole = key($auth->getAssignments($this->id));
                if($this->role != $orole){
                    $role = $auth->getRole($orole);
                    $auth->revoke($role, $this->id);
                    //更新角色
                    $role = $auth->getRole($this->role);
                    $auth->assign($role, $this->id);
                }
            } 
        }
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }
    
    public function rules()
    {
        return [
            //特殊需求
            [['password_hash'], 'required'],
            [['role'], 'required', 'on' => ['create','update']],
            [['password_reset_token'], 'unique', 'on' => ['create','update']],

             [['phone_number'],'UniquePhone','on'=>['create','update']],
            //字段规范
            ['phone_number', 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9])[0-9]{8}$/', 'message' => '请填写有效手机号。'],

            [['name'],'UniqueName','on'=>['modify','perfect']],

            ['password_hash', 'string', 'min' => 6],
            ['auth_key', 'default', 'value' => self::AUTH_KEY], 
            ['is_all_visible', 'default', 'value' => self::ALL_VISIBLE],
            ['is_main', 'default', 'value' => self::MAIN_NOT_YET],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['gender', 'in', 'range' => [self::GENDER_MALE, self::GENDER_FEMALE]],
            //字段类型
            [['is_all_visible', 'is_first','studio_id','is_main', 'created_at', 'updated_at', 'status','is_chat','is_cloud','is_create','is_sell'], 'integer'],
            ['is_first', 'default', 'value'  => 0],
            ['is_chat', 'default', 'value'  => 1],
            ['is_create', 'default', 'value'  => 1],
            ['is_cloud', 'default', 'value' => 1],
            ['phone_number', 'string', 'max' => 11],
            [['name', 'auth_key','qrcode'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 100],
            [['campus_id', 'category_id', 'class_id','sell_num'], 'safe'],
             [['usersig'], 'string', 'max' => 500],
            [['username'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'role' => Yii::t('app', '角色'),
            'phone_number' => Yii::t('app', '手机号'),
            'name' => Yii::t('app', '姓名'),
            'campus_id' => Yii::t('app', '可见校区'),
            'category_id' => Yii::t('app', '可见科目'),
            'class_id' => Yii::t('app', '可见班级'),
            'is_all_visible' => Yii::t('app', '是否全部可见'),
            'is_main' => Yii::t('app', '是否为主要数据'),
            'gender'  => Yii::t('app', '性别'),
            'auth_key' => Yii::t('app', '认证密钥'),
            'password_hash' => Yii::t('app', '密码'),
            'password_reset_token' => Yii::t('app', '密码重置Token'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
            'is_sell' => Yii::t('app','是否为销售'),
            'sell_num' => Yii::t('app','销售比例(%)'),
        ];
    }

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

    //姓名
    public function UniqueName($attribute)
    {
        $studio = $this->studio_id;

        $campus = Campus::find()
        ->select('id')
        ->where(['studio_id'=>$studio,'status'=>10])
        ->asArray()
        ->all();
        $campus_id = array_column($campus, 'id');

        $list =  Admin::find()
        ->select('name')
        ->where(['status'=>self::STATUS_ACTIVE])
        ->andWhere(['<>','id',$this->id])
        ->andFilterWhere(['or like',Format::concatField('campus_id'),Format::concatString($campus_id)])
        ->asArray()
        ->all();
        $name = array_column($list, 'name');  

        if(in_array($this->name, $name)) {
             $this->addError('name', "该昵称已经被占用");
        }else{
            return true;
        }       
       
    }
    
    /**
     * 根据ID查询用户
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的用户对象
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * 根据 token 查询用户 RESTFUL认证使用
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的用户对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * 验证登录密钥
     * 
     * @param string $authKey
     * @return boolean 如果当前用户身份验证的密钥是有效的
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 根据手机号查询用户
     *
     * @param string $phone_number
     * @return static|null
     */
    public static function findByPhoneNumber($phone_number)
    {

        return static::findOne(['phone_number' => $phone_number, 'status' => self::STATUS_ACTIVE]);

    }

    public static function GetByPhoneNumber($phone_number,$studio_id = NULL)
    {

        return static::find()
        ->where(['phone_number' => $phone_number, 'status' => self::STATUS_ACTIVE])
        ->andFilterWhere(['studio_id'=>$studio_id])
        ->one();
    }

    /**
     * 密码验证
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * 生成并设置密码
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 生成 记住我 的认证密钥
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'is_all_visible' => [
                self::MYSELF_VISIBLE => Yii::t('backend', 'Myself Visible'),
                self::ALL_VISIBLE => Yii::t('backend', 'All Visible'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ],
            'is_sell' => [
                self::SELL_YES => Yii::t('backend', 'IS SELL'),
                self::SELL_NO => Yii::t('backend',  'NOT SELL'),                
            ],
            'gender' => [
                self::GENDER_MALE => Yii::t('common', 'Male'),
                self::GENDER_FEMALE => Yii::t('common', 'Female'),
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function convertField()
    {
        $this->role = key(Yii::$app->authManager->getAssignments($this->id));
        if($this->campus_id){
            $this->campus_id = Format::explodeValue($this->campus_id);
        }
        if($this->category_id){
            $this->category_id = Format::explodeValue($this->category_id);
        }
        if($this->class_id){
            $this->class_id = Format::explodeValue($this->class_id);
        }
        return $this;
    }

    public function isAllVisible()
    {
        $this->is_all_visible = ($this->is_all_visible) ? self::MYSELF_VISIBLE : self::ALL_VISIBLE;
        if($this->save()){
            return true;
        }
        return false;
    }

    public static function getAdminList($campus_id = NULL)
    {
        if(!$campus_id){
            $campus_id = Yii::$app->user->identity->campus_id;
        }
        $model = static::find()
            ->andFilterWhere(['or like', Format::concatField('campus_id'), Format::concatString($campus_id)])
            ->andFilterWhere(['status' => self::STATUS_ACTIVE])
            ->all();
        foreach ($model as $v) {
            $role = Yii::$app->authManager->getRolesByUser($v->id);
            $res[$role[key($role)]->description][$v->id] = '　' . $v->name;
        }
        return ($res) ? $res: [];
    }

    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(), ['relation_id'=>'id'])->where(['type' => 1])->alias('codes');
    }

    public function setUuid()
    {
        $chars = md5(uniqid(mt_rand(), true));  
        $uuid  = substr($chars, 0, 8) . '-';  
        $uuid .= substr($chars, 8, 4) . '-';  
        $uuid .= substr($chars, 12, 4) . '-';  
        $uuid .= substr($chars, 16, 4) . '-';  
        $uuid .= substr($chars, 20, 12);
        $this->token_value = md5($uuid);
    }
}