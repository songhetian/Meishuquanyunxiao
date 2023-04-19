<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%app}}".
 *
 * @property integer $id
 * @property integer $studio_id
 * @property string $ipa
 * @property string $plist
 * @property string $apk
 * @property string $logo
 * @property string $android_version
 * @property string $ios_version
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class App extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%app}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //特殊需求
            [['studio_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['studio_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['ipa', 'plist', 'apk', 'logo'], 'string', 'max' => 200],
            [['android_version', 'ios_version'], 'string', 'max' => 32],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'studio_id' => Yii::t('app', '所属画室'),
            'ipa' => Yii::t('app', 'IPA文件'),
            'plist' => Yii::t('app', 'Plist文件'),
            'apk' => Yii::t('app', 'APK文件'),
            'logo' => Yii::t('app', 'Logo'),
            'android_version' => Yii::t('app', '安卓版本号'),
            'ios_version' => Yii::t('app', '苹果版本号'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
    public function getStudios()
    {
        return $this->hasOne(Studio::className(), ['id' => 'studio_id'])->alias('studios');
    }

}
