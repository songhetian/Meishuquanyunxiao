<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%family}}".
 *
 * @property int $id
 * @property string $phone_number 手机号
 * @property int $relation_id 关联学生账号
 * @property string $name 家长姓名
 * @property int $updated_at 创建时间
 * @property int $created_at 更改时间
 * @property int $status 状态
 */
class Family extends ActiveRecord
{
    const GENDER_MALE = 10;
    const GENDER_FEMALE = 20;

    public function beforeSave($insert)
    {        
        if ($this->isNewRecord) {
            $this->created_at = time();
            $this->updated_at = time();
            $this->setUuid();
        }else{
            $this->updated_at = time();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%family}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone_number'], 'required','on'=>['create']],
            [['relation_id', 'updated_at', 'created_at','campus_id','province','studio_id','is_first'], 'integer'],
            [['phone_number'], 'string', 'max' => 11],
            ['phone_number','UniquePhone','on'=>'modify'],
            [['name'],'UniqueName','on'=>['modify']],
            [['phone_number'], 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9])[0-9]{8}$/', 'message'=>'请填写有效手机号。'],
            [['name','image','vip_time'], 'string', 'max' => 100],
            ['usersig','string','max' => 500],
            ['qrcode','string','max' => 32],
            ['status', 'default',   'value' => self::STATUS_ACTIVE],
            ['is_first', 'default', 'value' => 0],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['gender', 'in', 'range' => [self::GENDER_MALE, self::GENDER_FEMALE]],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
                'maxFiles' => 100

            ],
        ];
    }

    //手机验证码验证
    public function UniquePhone($attribute)
    {
        $list =  self::find()
                     ->select('phone_number')
                     ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$this->studio_id])
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

    //名字不能重复
    public function UniqueName($attribute)
    {
        $list =  self::find()
                     ->select('name')
                     ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$this->studio_id])
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

    
    public static function getValues($field, $value = false)
    {
        $values = [
            'gender' => [
                self::GENDER_MALE => Yii::t('common', 'Male'),
                self::GENDER_FEMALE => Yii::t('common', 'Female'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE =>  Yii::t('backend',  'Not Deleted'),                
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone_number' => '手机号码',
            'relation_id' => '关联id',
            'name' => '名字',
            'image' => '用户头像',
            'updated_at' => '创建时间',
            'created_at' => '修改时间',
            'status' => '状态',
        ];
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
