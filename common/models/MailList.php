<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%mail_list}}".
 *
 * @property int $id
 * @property int $user_id 关联用户
 * @property int $role 角色
 * @property int $old 旧值
 * @property int $new 新值
 * @property int $studio_id 画室id
 * @property integer $created_at
 * @property integer $updated_at
 * @property int $status 状态
 */
class MailList extends \backend\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mail_list}}';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'role', 'studio_id'], 'required'],
            [['user_id', 'role', 'old', 'new', 'studio_id', 'created_at', 'updated_at', 'status'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['old', 'default', 'value' => 0],
            ['new', 'default', 'value' => 1]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'role' => 'Role',
            'old' => 'Old',
            'new' => 'New',
            'studio_id' => 'Studio ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
