<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%chat_group}}".
 *
 * @property int $id
 * @property string $group_id 群组标识
 * @property string $command 口令
 * @property int $studio_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 */
class ChatGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%chat_group}}';
    }
    // public function afterSave($insert, $changedAttributes)
    // {
    //     parent::afterSave($insert, $changedAttributes);
    //     \backend\models\AdminLog::saveLog($this);
    //     return true; 
    // }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            [['studio_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['group_id', 'command'], 'required'],
            ['group_id', 'unique'],
            [['group_id', 'command'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => '群组id',
            'command' => '口令',
            'studio_id' => 'Studio ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
