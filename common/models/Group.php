<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
/**
 * This is the model class for table "{{%group}}".
 *
 * @property integer $id
 * @property integer $course_material_id
 * @property integer $type
 * @property string $name
 * @property string $material_library_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Group extends ActiveRecord
{
    const TYPE_PICTURE = 10;
    const TYPE_VIDEO = 20;

    public static function tableName()
    {
        return '{{%group}}';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['course_material_id', 'type', 'name'], 'required'],
            //字段规范
            ['type', 'in', 'range' => [self::TYPE_PICTURE, self::TYPE_VIDEO]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['course_material_id', 'type', 'created_at', 'updated_at', 'status'], 'integer'],
            [['material_library_id'], 'string'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'course_material_id' => Yii::t('app', '所属教案'),
            'type' => Yii::t('app', '类型'),
            'name' => Yii::t('app', '名称'),
            'material_library_id' => Yii::t('app', '素材'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getGroupList($course_material_id, $type, $status = 1)
    {
        $res = static::findAll([
            'course_material_id' => $course_material_id,
            'type' => $type,
            'status' => self::STATUS_ACTIVE
        ]);
        return ($res) ? $res : [];
    }
}
