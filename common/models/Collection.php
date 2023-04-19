<?php

namespace common\models;

use Yii;
use components\Alidayu;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%collection}}".
 *
 * @property int $id
 * @property string $name 校长姓名
 * @property string $phone_number 校长手机号
 * @property string $studio_name 画室名
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $status 状态
 */
class Collection extends ActiveRecord
{
    public $phone_verify_code;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%collection}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'phone_number', 'studio_name','phone_verify_code'], 'required'],
            [['name', 'phone_number', 'studio_name'], 'unique'],
            [['created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'studio_name'], 'string', 'max' => 100],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['is_review', 'default', 'value' => self::STATUS_DELETED],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['is_review', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['phone_number', 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9])[0-9]{8}$/', 'message' => '请填写有效手机号。'],
            //手机验证码
            ['phone_verify_code','phoneVerifyCode'],
        ];
    }

    //手机验证码验证
    public function phoneVerifyCode($attribute)
    {
        if($this->phone_number && $this->phone_verify_code) {
            $alidayu = new Alidayu();
            $res = $alidayu->phoneVerifyCode($this);
            if($res == false){
                $this->addError('phone_verify_code', '无效的验证码');
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'name'         => '校长姓名',
            'phone_number' => '手机号',
            'studio_name'  => '画室名',
            'is_review'    => '审核状态',
            'created_at'   => '创建时间',
            'updated_at'   => '修改时间',
            'status'       => '状态',
        ];
    }
}
