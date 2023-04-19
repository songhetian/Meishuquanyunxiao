<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%message_category}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $icon
 * @property integer $priority
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class MessageCategory extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%message_category}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['name'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['priority', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name'], 'string', 'max' => 20],
            ['icon', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'maxSize' => 1024 * 5000,
                'minWidth' => 150,
                'minHeight' => 150,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '名称'),
            'icon' => Yii::t('app', '图标'),
            'priority' => Yii::t('app', '优先级'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
    
    public static function getMessageCategoryList()
    {
        $model = static::find()
            ->andFilterWhere(['!=', 'name', Yii::t('common', 'My Msg')])
            ->andFilterWhere(['!=', 'name', Yii::t('common', 'System Msg')])
            ->andFilterWhere(['status' => self::STATUS_ACTIVE])
            ->orderBy('priority, id')
            ->all();
        return ArrayHelper::map($model, 'id', 'name');
    }
}
