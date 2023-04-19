<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_follow".
 *
 * @property integer $user_follow_id
 * @property string $follow_user_type
 * @property integer $follow_user_id
 * @property integer $timer
 * @property integer $status
 * @property integer $user_id
 * @property integer $studio_id
 * @property string $user_type
 */
class UserFollow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_follow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['follow_user_id', 'timer', 'status', 'user_id', 'studio_id'], 'integer'],
            [['follow_user_type', 'user_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_follow_id' => 'User Follow ID',
            'follow_user_type' => 'Follow User Type',
            'follow_user_id' => 'Follow User ID',
            'timer' => 'Timer',
            'status' => 'Status',
            'user_id' => 'User ID',
            'studio_id' => 'Studio ID',
            'user_type' => 'User Type',
        ];
    }
}
