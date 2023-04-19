<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "registration".
 *
 * @property integer $registration_id
 * @property string $name
 * @property integer $studio_id
 * @property string $lat
 * @property string $lng
 * @property string $pic
 * @property integer $phone_number
 */
class Registration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'registration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['studio_id', 'phone_number'], 'integer'],
            [['name', 'lng'], 'string', 'max' => 255],
            [['lat'], 'string', 'max' => 100],
            [['pic'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'registration_id' => 'Registration ID',
            'campus_id' => '校区id',
            'name' => '画室名称',
            'studio_id' => 'Studio ID',
            'lat' => '纬度',
            'lng' => '经度',
            'pic' => '画室展示图',
            'phone_number' => '报名电话',
            'address' => '详细地址'
        ];
    }
}
