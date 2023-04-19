<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\ActiveRecord;
use backend\models\Admin;
use common\models\LeaveAudit;
use common\models\User;
use common\models\Format;

/**
 * This is the model class for table "{{%leave}}".
 *
 * @property integer $id
 * @property string $user_role
 * @property integer $type
 * @property integer $started_at
 * @property integer $ended_at
 * @property double $day
 * @property string $reason
 * @property string $image
 * @property integer $account_id
 * @property integer $is_urged
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Leave extends ActiveRecord
{
    public $audit_id;

    const TYPE_CASUAL = 10;
    const TYPE_SICK = 20;
    const TYPE_ANNUAL = 30;
    const TYPE_PAID = 40;
    const TYPE_MARRIAGE = 50;
    const TYPE_MATERNITY = 60;
    const TYPE_PATERNITY = 70;
    const TYPE_ROAD = 80;
    const TYPE_OTHER = 90;

    const URGED_NOT_YET = 0;
    const URGED_ED = 10;

    public static function tableName()
    {
        return '{{%leave}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->started_at = strtotime($this->started_at);
                $this->ended_at = strtotime($this->ended_at);
                if($this->image){
                    foreach ($this->image as $url) {
                        $exps = explode('?', urldecode($url));
                        $name = explode('/', $exps[0]);
                        $arr[] = end($name);
                    }
                    $this->image = Format::implodeValue($arr);
                }
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //创建对应审核人数据
        if($insert) {
            $number = 1;
            $enc = json_encode($this->audit_id);
            $res = json_decode($enc);
            //$res = json_decode($this->audit_id);
            if($res){
                foreach ($res as $v) {
                    $audit = new LeaveAudit();
                    $audit->leave_id = $this->id;
                    $audit->user_role = $v->user_role;
                    if($number == 1){
                        $audit->processing_state = LeaveAudit::PROCESSING_STATE_NOT_YET;
                    }
                    $audit->audit_id = $v->id;
                    $audit->position = $number;
                    $audit->save();
                    $number++;
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
            ['audit_id', 'required', 'on' => 'create'],
            //[['user_role', 'type', 'account_id', 'started_at', 'ended_at', 'day', 'reason'], 'required'],
            [['user_role', 'type', 'account_id', 'started_at', 'ended_at', 'reason'], 'required'],
            //字段规范
            ['is_urged', 'default', 'value' => self::URGED_NOT_YET], 
            ['is_urged', 'in', 'range' => [self::URGED_NOT_YET, self::URGED_ED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['type', 'account_id', 'is_urged', 'created_at', 'updated_at', 'status'], 'integer'],
            [['day'], 'number'],
            [['user_role', 'reason'], 'string'],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_role' => Yii::t('app', '身份'),
            'type' => Yii::t('app', '类型'),
            'started_at' => Yii::t('app', '开始时间'),
            'ended_at' => Yii::t('app', '结束时间'),
            'day' => Yii::t('app', '天数'),
            'reason' => Yii::t('app', '事由'),
            'image' => Yii::t('app', '图片'),
            'account_id' => Yii::t('app', '请假人'),
            'audit_id' => Yii::t('app', '审批人'),
            'is_urged' => Yii::t('app', '是否促办'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'type' => [
                self::TYPE_CASUAL => Yii::t('backend', 'Casual'),
                self::TYPE_SICK => Yii::t('backend', 'Sick'),
                self::TYPE_ANNUAL => Yii::t('backend', 'Annual'),
                self::TYPE_PAID => Yii::t('backend', 'Paid'),
                self::TYPE_MARRIAGE => Yii::t('backend', 'Marriage'),
                self::TYPE_MATERNITY => Yii::t('backend', 'Maternity'),
                self::TYPE_PATERNITY => Yii::t('backend', 'Paternity'),
                self::TYPE_ROAD => Yii::t('backend', 'Road'),
                self::TYPE_OTHER => Yii::t('backend', 'Other'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ]
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'account_id'])->alias('users');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'account_id'])->alias('admins');
    }
}