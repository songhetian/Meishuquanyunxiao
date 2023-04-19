<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;
use common\models\Category;

/**
 * This is the model class for table "{{%keyword}}".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $category_id
 * @property string $name
 * @property integer $priority
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Keyword extends ActiveRecord
{
    const TYPE_PICTURE = 10;
    const TYPE_VIDEO = 20;

    public static function tableName()
    {
        return '{{%keyword}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['type', 'category_id', 'name'], 'required'],
            //字段规范
            ['type', 'in', 'range' => [self::TYPE_PICTURE, self::TYPE_VIDEO]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
             //字段类型
            [['type', 'category_id', 'priority', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', '类型'),
            'category_id' => Yii::t('app', '分类'),
            'name' => Yii::t('app', '名称'),
            'priority' => Yii::t('app', '优先级'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getKeywordList($category_id = 0, $type = self::TYPE_PICTURE)
    {
        $model = static::find()
            ->where(['category_id' => $category_id, 'type' => $type, 'status' => self::STATUS_ACTIVE])
            ->orderBy('priority, id')
            ->all();
        return ArrayHelper::map($model, 'id', 'name');
    }

    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }
}
