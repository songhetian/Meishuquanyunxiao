<?php

namespace common\models;

use Yii;

class UserVipPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_vip_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['membership_type', 'price', 'price_ios'], 'required'],
            [['status','membership_type'], 'integer', 'max' => 2147483646],
            [['price'], 'number'],
            [['product_id', 'hd_product_id'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'membership_type' => Yii::t('app', '会员类型'),
            'name' => Yii::t('app', '名称'),
            'price' => Yii::t('app', '价格.'),
            'price_ios' => Yii::t('app', 'IOS价格'),
            'product_id' => Yii::t('app', 'IAP价格ID'),
            'hd_product_id' => Yii::t('app', 'HDIAP价格ID'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
