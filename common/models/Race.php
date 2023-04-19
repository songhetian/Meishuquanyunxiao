<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%race}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Race extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%race}}';
    }

    public function rules()
    {
        return [
        	['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            [['name'], 'required'],
            [['created_at', 'updated_at', 'status'], 'integer'],
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

    public static function getRaceList()
    {
        $model = static::findAll(['status' => self::STATUS_ACTIVE]);
        return ArrayHelper::map($model, 'id', 'name');
    }
}