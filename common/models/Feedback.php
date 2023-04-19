<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%feedback}}".
 *
 * @property integer $id
 * @property string $type
 * @property string $manufacturer_model
 * @property string $system_version
 * @property string $app_version
 * @property string $network_state
 * @property string $longitude
 * @property string $latitude
 * @property string $content
 * @property string $image
 * @property string $contact
 * @property integer $feedback_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Feedback extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%feedback}}';
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
            [['type', 'contact', 'feedback_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['feedback_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['content'], 'string'],
            [['type', 'manufacturer_model', 'system_version', 'app_version', 'network_state', 'longitude', 'latitude'], 'string', 'max' => 100],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', '类型'),
            'manufacturer_model' => Yii::t('app', '设备信息'),
            'system_version' => Yii::t('app', '系统版本'),
            'app_version' => Yii::t('app', '软件版本'),
            'network_state' => Yii::t('app', '网络状态'),
            'longitude' => Yii::t('app', '精度'),
            'latitude' => Yii::t('app', '纬度'),
            'content' => Yii::t('app', '内容'),
            'image' => Yii::t('app', '图片'),
            'contact' => Yii::t('app', '联系方式'),
            'feedback_id' => Yii::t('app', '反馈人'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
