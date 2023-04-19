<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "daily".
 *
 * @property integer $daily_id
 * @property string $name
 * @property string $avatar
 * @property string $image_url_came
 * @property integer $timer
 * @property integer $status
 * @property string $content
 * @property integer $user_id
 * @property integer $studio_id
 * @property integer $views
 * @property string $user_type
 */
class Daily extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'daily';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image_url_came', 'content'], 'string'],
            [['timer', 'status', 'user_id', 'studio_id', 'views'], 'integer'],
            [['user_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'daily_id' => '动态ID',
            'image_url_came' => 'Image Url Came',
            'timer' => 'Timer',
            'status' => 'Status',
            'content' => 'Content',
            'user_id' => 'User ID',
            'studio_id' => 'Studio ID',
            'views' => 'Views',
            'user_type' => 'User Type',
        ];
    }
}
