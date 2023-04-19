<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%course}}".
 *
 * @property integer $id
 * @property integer $class_period_id
 * @property integer $class_id
 * @property integer $category_id
 * @property integer $instructor
 * @property integer $instruction_method_id
 * @property integer $course_material_id
 * @property integer $started_at
 * @property integer $ended_at
 * @property string $class_content
 * @property string $class_emphasis
 * @property string $note
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class SimpleCourse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'started_at', 'ended_at', 'admin_id', 'created_at', 'updated_at'], 'required'],
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'course_material_id', 'started_at', 'ended_at', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['class_content', 'class_emphasis', 'note'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class_period_id' => 'Class Period ID',
            'class_id' => 'Class ID',
            'category_id' => 'Category ID',
            'instructor' => 'Instructor',
            'instruction_method_id' => 'Instruction Method ID',
            'course_material_id' => 'Course Material ID',
            'started_at' => 'Started At',
            'ended_at' => 'Ended At',
            'class_content' => 'Class Content',
            'class_emphasis' => 'Class Emphasis',
            'note' => 'Note',
            'admin_id' => 'Admin ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
