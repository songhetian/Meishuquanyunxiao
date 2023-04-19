<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%error_log}}".
 *
 * @property int $id
 * @property int $studio_id
 * @property int $admin_id
 * @property int $role
 * @property string $app_version
 * @property string $model 型号
 * @property string $manufacturer 制造商
 * @property string $incermental
 * @property string $code_name
 * @property int $sdk_int
 * @property string $crash_time
 * @property string $crash_info
 * @property string $simple_crash_info
 * @property int $created_at
 * @property int $updated_at
 * @property int $status 状态
 */
class ErrorLog extends \backend\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%error_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            #[['studio_id', 'incermental', 'code_name'], 'required'],
            [['studio_id', 'admin_id', 'sdk_int', 'created_at', 'updated_at', 'role','status'], 'integer'],
            [['crash_info', 'simple_crash_info'], 'string'],
            [['app_version', 'model', 'manufacturer', 'incermental', 'code_name', 'crash_time'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studio_id' => 'Studio ID',
            'admin_id' => 'Admin ID',
            'role' => 'Role',
            'app_version' => 'App Version',
            'model' => 'Model',
            'manufacturer' => 'Manufacturer',
            'incermental' => 'Incermental',
            'code_name' => 'Code Name',
            'sdk_int' => 'Sdk Int',
            'crash_time' => 'Crash Time',
            'crash_info' => 'Crash Info',
            'simple_crash_info' => 'Simple Crash Info',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
    public function getStudios()
    {
        return $this->hasOne(Studio::className(), ['id' => 'studio_id'])->alias('studios');
    }
}
