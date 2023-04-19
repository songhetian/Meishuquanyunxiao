<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%leave_rule}}".
 *
 * @property integer $id
 * @property integer $studio_id
 * @property integer $campus_id
 * @property string $student
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class LeaveRule extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%leave_rule}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['studio_id', 'campus_id', 'student'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['studio_id', 'campus_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['student'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'studio_id' => Yii::t('app', '所属画室'),
            'campus_id' => Yii::t('app', '所属校区'),
            'student' => Yii::t('app', '学生请假规则'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
