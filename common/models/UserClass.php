<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%user_class}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $class_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class UserClass extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_class}}';
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
            [['user_id', 'class_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['user_id', 'class_id', 'created_at', 'updated_at', 'status'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户'),
            'class_id' => Yii::t('app', '班级'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}