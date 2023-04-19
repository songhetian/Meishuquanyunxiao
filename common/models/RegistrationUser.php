<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "registration_user".
 *
 * @property integer $studio_id
 * @property integer $user_id
 * @property string $user_type
 * @property integer $timer
 * @property integer $status
 */
class RegistrationUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'registration_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['studio_id'], 'required'],
            [['studio_id', 'user_id', 'timer', 'status'], 'integer'],
            [['user_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'studio_id' => 'Studio ID',
            'user_id' => 'User ID',
            'user_type' => 'User Type',
            'timer' => '报名时间',
            'status' => 'Status',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->alias('user');
    }
}
