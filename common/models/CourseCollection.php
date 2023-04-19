<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%course_collection}}".
 *
 * @property int $id 主键
 * @property int $admin_id 收藏者id
 * @property int $role 角色10老师20学生
 * @property string $material_id 资源id
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $status 状态
 * @property int $studio_id 画室id
 */
class CourseCollection extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%course_collection}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'studio_id'], 'required'],
            [['admin_id', 'role', 'material_id','created_at', 'updated_at', 'teacher_id','studio_id'], 'integer'],
            //字段规范
            ['role', 'default','value' => 20], 
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => '创建人',
            'role' => '角色',
            'teacher_id' => '老师',
            'material_id' => '资源',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'status' => '状态',
            'studio_id' => '画室',
        ];
    }
}
