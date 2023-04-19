<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\Admin;
use backend\models\ActiveRecord;
use common\models\Format;

/**
 * This is the model class for table "{{%class_period}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $studio_id
 * @property string $started_at
 * @property string $dismissed_at
 * @property integer $position
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class ClassPeriod extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%class_period}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $campus_id = Format::explodeValue(Admin::findOne(Yii::$app->user->identity->id)->campus_id);
                $this->studio_id = Campus::findOne(current($campus_id))->studio_id;
            }else{
            }
            return true;
        }
        return false;
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
            [['name', 'started_at', 'dismissed_at', 'position'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['studio_id', 'position', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'started_at', 'dismissed_at'], 'string', 'max' => 32],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '名称'),
            'studio_id' => Yii::t('app', '所属画室'),
            'started_at' => Yii::t('app', '上课时间'),
            'dismissed_at' => Yii::t('app', '下课时间'),
            'position' => Yii::t('app', '位置'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    static function getTimeList(){
        $res = [];
        for ($i = 7; $i < 24; $i++) { 
            $res[] .= $i.":00";
            $res[] .= $i.":30";
        }
        $res[] .= "24:00";
        return $res;
    }

    static function getClassPeriodList()
    {
        $res = [];

        $model = static::findAll(['studio_id' => Format::getStudio('id'), 'status' => self::STATUS_ACTIVE]);
        if($model){
            foreach ($model as $v) {
                $res[$v->id] = $v->name . ' ('. $v->started_at .' - ' . $v->dismissed_at.'）';
            }
        }
        return $res;
    }
}
