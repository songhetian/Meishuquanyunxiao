<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;
use common\models\Format;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $pid
 * @property string $name
 * @property integer $level
 * @property string $color
 * @property integer $priority
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Category extends ActiveRecord
{
    const TYPE_PICTURE = 10;
    const TYPE_VIDEO = 20;

    public static function tableName()
    {
        return '{{%category}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['type', 'name'], 'required'],
            //字段规范
            ['type', 'in', 'range' => [self::TYPE_PICTURE, self::TYPE_VIDEO]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['studio_type', 'default', 'value' => 1], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['type', 'pid', 'level', 'priority', 'created_at', 'updated_at', 'status','studio_type'], 'integer'],
            [['name', 'color'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', '类型'),
            'pid' => Yii::t('app', '父类'),
            'name' => Yii::t('app', '名称'),
            'level' => Yii::t('app', '等级'),
            'color' => Yii::t('app', '颜色'),
            'priority' => Yii::t('app', '优先级'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getCategoryList($pid = 0, $type = self::TYPE_PICTURE)
    {
        $category_id = Yii::$app->user->identity->category_id;
        $model = static::find()
            ->andFilterWhere(['id' => Format::explodeValue($category_id)])
            ->andFilterWhere(['pid' => $pid, 'type' => $type, 'status' => self::STATUS_ACTIVE])
            ->orderBy('priority, id')
            ->all();
        return ArrayHelper::map($model, 'id', 'name');
    }

    public static function getCategoryChildList($type = self::TYPE_PICTURE)
    {
        $res = [];
        $model = static::find()
            ->andFilterWhere(['type' => $type, 'status' => self::STATUS_ACTIVE])
            ->andFilterWhere(['!=', 'level', 0])
            ->orderBy('priority, id')
            ->all();

        if($model){
            foreach ($model as $v) {
                $res[$v->categorys->name][$v->id] = '　—　' . $v->name;
            }
        }
        return $res;
    }

    
    public function getCategorys()
    {
        return $this->hasOne(self::className(), ['id' => 'pid'])->alias('categorys');
    }
}
