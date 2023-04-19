<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%studio_power}}".
 *
 * @property int $id
 * @property int $studio_id
 * @property int $people_look_daily
 * @property int $people_add_daily
 * @property int $people_comment_daily
 * @property int $student_add_daily
 * @property int $student_comment_daily
 */
class StudioPower extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%studio_power}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['studio_id', 'people_look_daily', 'people_add_daily', 'people_comment_daily', 'student_add_daily', 'student_comment_daily'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studio_id' => 'Studio ID',
            'people_look_daily' => 'People Look Daily',
            'people_add_daily' => 'People Add Daily',
            'people_comment_daily' => 'People Comment Daily',
            'student_add_daily' => 'Student Add Daily',
            'student_comment_daily' => 'Student Comment Daily',
        ];
    }
}
