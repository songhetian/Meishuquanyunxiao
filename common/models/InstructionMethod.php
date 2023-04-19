<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%instruction_method}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class InstructionMethod extends ActiveRecord
{
    public static function tableName()
    {
        return 'instruction_method';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            [['created_at', 'updated_at', 'status','type'], 'integer'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '名称'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
    
    public static function getInstructionMethodList()
    {
        $model = static::findAll(['status' => self::STATUS_ACTIVE]);
        return ArrayHelper::map($model, 'id', 'name');
    }
}
