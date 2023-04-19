<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%Invitation}}".
 *
 * @property int $id
 * @property int $invite_id 邀请人id
 * @property int $role 邀请人身份
 * @property int $invitee_id 被邀请id
 * @property int $invitee_role 被邀请人身份
 * @property int $give_days 赠送天数
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $status 状态
 */
class Invitation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%Invitation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invite_id', 'role', 'invitee_id', 'invitee_role', 'give_days'], 'required'],

            [['invite_id', 'role', 'invitee_id', 'invitee_role', 'give_days', 'created_at', 'updated_at', 'status'], 'integer'],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],

            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],

            ['role', 'in', 'range' => [10,20,30]],

            ['invitee_role', 'in', 'range' => [10,20,30]]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invite_id' => 'Invite ID',
            'role' => 'Role',
            'invitee_id' => 'Invitee ID',
            'invitee_role' => 'Invitee Role',
            'give_days' => 'Give Days',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
