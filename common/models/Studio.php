<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use common\models\App;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%studio}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $review_num
 * @property string $jpush_app_key
 * @property string $jpush_master_secret
 * @property string $image
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Studio extends ActiveRecord
{
    const SOURCE_NOT_VIEW = 0;
    const SOURCE_VIEW = 10;

    public static function tableName()
    {
        return '{{%studio}}';
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
            [['name'], 'required'],
            [['bind_code','name'],'unique'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['teacher_num', 'default', 'value' => 50],
            ['type', 'default', 'value' =>  1],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['is_view', 'in', 'range' => [self::SOURCE_NOT_VIEW, self::SOURCE_VIEW]],
            //字段类型
            [['one_year_num', 'two_years_num','three_years_num','teacher_num','review_num','created_at', 'updated_at', 'is_press','status','type','six_month_num','one_month_num','three_month_num'], 'integer'],
            [['name', 'jpush_app_key', 'jpush_master_secret','bind_code'], 'string', 'max' => 32],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'maxSize' => 1024 * 5000,
                'minWidth' => 150,
                'minHeight' => 150,
            ],
            [['name'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '画室名称'),
            'one_year_num' => Yii::t('app', '购买数量'),
            'jpush_app_key' => Yii::t('app', '极光 AppKey'),
            'jpush_master_secret' => Yii::t('app', '极光 MasterSecret'),
            'is_view' => Yii::t('app', '是否显示素材库'),
            'image' => Yii::t('app', '背景图'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getByName($name) {
        $model = self::find()
                 ->select('id')
                 ->where(['name'=>$name,'status'=>Gather::STATUS_ACTIVE])
                 ->one();
        return $model->id;
    }

    public static function getList()
    {
       
        $model = self::find()
                 ->select('id,name')
                 ->where(['status'=>Gather::STATUS_ACTIVE])
                 ->all();
                 
        return ArrayHelper::map($model, 'id', 'name');
    }

}