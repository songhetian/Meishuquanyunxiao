<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_like".
 *
 * @property integer $user_like_id
 * @property string $like_type
 * @property integer $like_id
 * @property integer $timer
 * @property integer $status
 * @property integer $user_id
 * @property integer $studio_id
 * @property string $user_type
 */
class UserLike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_like';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['like_id', 'timer', 'status', 'user_id', 'studio_id'], 'integer'],
            [['like_type', 'user_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_like_id' => 'User Like ID',
            'like_type' => 'Like Type',
            'like_id' => 'Like ID',
            'timer' => 'Timer',
            'status' => 'Status',
            'user_id' => 'User ID',
            'studio_id' => 'Studio ID',
            'user_type' => 'User Type',
        ];
    }
}
