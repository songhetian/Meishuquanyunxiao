<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "follow_cc".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_type
 * @property string $cc_id
 * @property integer $status
 * @property integer $timer
 */
class FollowCc extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow_cc';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'timer'], 'integer'],
            [['user_type', 'cc_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_type' => 'User Type',
            'cc_id' => 'Cc ID',
            'status' => 'Status',
            'timer' => 'Timer',
        ];
    }
}
