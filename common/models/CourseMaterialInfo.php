<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%course_material_info}}".
 *
 * @property integer $id
 * @property integer $course_material_id
 * @property integer $category_id
 * @property string $class_content
 * @property integer $instruction_method_id
 */
class CourseMaterialInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_material_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_material_id', 'category_id','instruction_method_id','admin_id'], 'required'],
            [['course_material_id', 'category_id', 'instruction_method_id','admin_id'], 'integer'],
            [['class_content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_material_id' => '教案',
            'category_id' => '科目',
            'class_content' => '教学内容',
            'instruction_method_id' => '教学方式',
            'admin_id'     => '创建者',
        ];
    }
}
