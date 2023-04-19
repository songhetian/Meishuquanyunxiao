<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "daily_comment".
 *
 * @property integer $daily_comment_id
 * @property integer $daily_id
 * @property integer $daily_comment_pid
 * @property integer $timer
 * @property integer $status
 * @property string $content
 * @property integer $user_id
 * @property integer $studio_id
 * @property string $user_type
 */
class DailyComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'daily_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'daily_id', 'daily_comment_pid', 'timer', 'status', 'user_id', 'studio_id','reply_user_id'], 'integer'],
            [['content'], 'string'],
            [['user_type','reply_user_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'daily_comment_id' => 'Daily Comment ID',
            'daily_id' => 'Daily ID',
            'daily_comment_pid' => 'Daily Comment Pid',
            'timer' => 'Timer',
            'status' => 'Status',
            'content' => 'Content',
            'user_id' => 'User ID',
            'studio_id' => 'Studio ID',
            'user_type' => 'User Type',
            'reply_user_id' => 'reply_user_id',
            'reply_user_type'=> 'reply_user_type',
        ];
    }
}
