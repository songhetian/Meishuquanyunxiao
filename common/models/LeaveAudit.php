<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use common\models\User;
use common\models\Family;
use backend\models\Admin;

/**
 * This is the model class for table "{{%leave_audit}}".
 *
 * @property integer $id
 * @property integer $leave_id
 * @property string $user_role
 * @property integer $audit_id
 * @property integer $position
 * @property integer $processing_state
 * @property integer $processing_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class LeaveAudit extends ActiveRecord
{
    const PROCESSING_STATE_NULL = 0;
    const PROCESSING_STATE_NOT_YET = 10;
    const PROCESSING_STATE_MOVE = 20;
    const PROCESSING_STATE_ED = 30;
    const PROCESSING_STATE_REFUSE = 40;
    const PROCESSING_STATE_URGED = 50;
    const PROCESSING_STATE_CLOSE = 60;
    const PROCESSING_STATE_DELETE = 70;
    
    public static function tableName()
    {
        return '{{%leave_audit}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['leave_id', 'user_role', 'audit_id', 'position'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['processing_state', 'default', 'value' => self::PROCESSING_STATE_NULL],
            //字段类型
            [['leave_id', 'audit_id', 'position', 'processing_state', 'processing_at', 'created_at', 'updated_at', 'status'], 'integer'],
            ['user_role', 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'leave_id' => Yii::t('app', '请假ID'),
            'user_role' => Yii::t('app', '身份'),
            'audit_id' => Yii::t('app', '审核人'),
            'position' => Yii::t('app', '顺序'),
            'processing_state' => Yii::t('app', '审核状态'),
            'processing_at' => Yii::t('app', '审核时间'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    //获取审批人
    public static function getAudits($leave_id)
    {
        $res = self::find()
        ->andFilterWhere(['!=', 'processing_state',  self::PROCESSING_STATE_NULL])
        ->andFilterWhere(['leave_id' => $leave_id, 'status' => self::STATUS_ACTIVE])
        ->orderBy('position ASC')
        ->all();
        return $res;
    }

    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'audit_id'])->alias('users');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'audit_id'])->alias('admins');
    }

    public function getFamilys()
    {
        return $this->hasOne(Family::className(), ['id' => 'audit_id'])->alias('familys');
    }
}