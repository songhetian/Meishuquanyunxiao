<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "sign".
 *
 * @property integer $id
 * @property integer $class_id
 * @property integer $user_id
 * @property integer $course_id
 * @property integer $updated_at
 * @property integer $created_at
 * @property integer $status
 */
class Sign extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sign';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_id', 'user_id', 'course_id'], 'required'],
            [['class_id', 'user_id', 'course_id', 'updated_at', 'created_at','status'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class_id' => '班级',
            'user_id' => '用户',
            'course_id' => '课程',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'status' => 'Status',
        ];
    }

    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->alias('users');
    }

}
