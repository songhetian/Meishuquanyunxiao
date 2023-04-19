<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\Admin;
use backend\models\ActiveRecord;
use common\models\Query;
use common\models\Format;

/**
 * This is the model class for table "{{%campus}}".
 *
 * @property integer $id
 * @property integer $studio_id
 * @property string $name
 * @property integer $is_main
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Campus extends ActiveRecord
{
    const MAIN_NOT_YET = 0;
    const MAIN_ED = 10;

    public static function tableName()
    {
        return '{{%campus}}';
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
        //每创建新校区 对应校长添加该校区
        if($insert) {
            $campus_id = Campus::getCampuses($this->studio_id);
            $model = Admin::find()
            ->andFilterWhere(['or like', Format::concatField('campus_id'), Format::concatString($campus_id)])
            ->andFilterWhere(['is_main' => Admin::MAIN_ED])
            ->one();

            if($model){
                $model->convertField();
                $campus = $model->campus_id;
                $campus[] = $this->id;
                $model->campus_id = $campus;
                $model->save(false); 
            }
        }
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['name'], 'required'],
            //字段规范
            ['is_main', 'default', 'value' => self::MAIN_NOT_YET],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['studio_id', 'is_main', 'created_at', 'updated_at', 'status','phone_number'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['lat'], 'string', 'max' => 100],
            [['pic','address'], 'string', 'max' => 500],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'studio_id' => Yii::t('app', '所属画室'),
            'name' => Yii::t('app', '名称'),
            'is_main' => Yii::t('app', '是否为主要数据'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
            'lat' => Yii::t('app', '纬度'),
            'lng' => Yii::t('app', '经度'),
            'pic' => Yii::t('app', '校区展示图'),
            'phone_number' => Yii::t('app', '报名电话'),
            'address' => Yii::t('app', '详细地址'),
        ];
    }

    public static function getCampusList()
    {
        $campus_id = Yii::$app->user->identity->campus_id;
        $model = static::find()
            ->andFilterWhere(['id' => Format::explodeValue($campus_id)])
            ->andFilterWhere(['status' => self::STATUS_ACTIVE])
            ->all();
        return ArrayHelper::map($model, 'id', 'name');
    }

    public static function getCampusId()
    {
        $model = static::findAll(['status' => self::STATUS_ACTIVE]);
        foreach ($model as $v) {
            $res[] = $v->id;
        }
        return ($res) ? $res : [];
    }

    public static function getCampuses($studio_id)
    {
        $campuses = static::findAll(['studio_id' => $studio_id, 'status' => self::STATUS_ACTIVE]);
        foreach ($campuses as $campus) {
            $campus_id[] = $campus->id;
        }
        return $campus_id;
    }
}