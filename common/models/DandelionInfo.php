<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%dandelion_info}}".
 *
 * @property integer $id
 * @property string $build_id
 * @property string $app_id
 * @property string $app_name
 */
class DandelionInfo extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dandelion_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['build_id', 'app_id', 'app_name'], 'required'],
            [['build_id', 'app_id'], 'string', 'max' => 100],
            [['app_name'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'build_id' => 'Build ID',
            'app_id' => 'App ID',
            'app_name' => 'App Name',
        ];
    }
}
