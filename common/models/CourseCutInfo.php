<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
/**
 * This is the model class for table "{{%course_cut_info}}".
 *
 * @property integer $id
 * @property integer $course_id
 * @property integer $class_period_id
 * @property string $time
 */
class CourseCutInfo extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    const HANDEL_CUT  = 1;
    const HANDEL_DEL  = 2;
    const HANDEL_COPY = 3;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_cut_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'class_period_id', 'time'], 'required'],
            [['course_id', 'class_period_id'], 'integer'],
            [['time'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => '课程',
            'class_period_id' => '上课时间',
            'time' => '上课日期',
        ];
    }
}
