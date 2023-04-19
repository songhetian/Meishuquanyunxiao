<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "wechat_order".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $appid
 * @property string $mch_id
 * @property string $device_info
 * @property string $nonce_str
 * @property string $sign
 * @property string $body
 * @property string $detail
 * @property string $attach
 * @property string $out_trade_no
 * @property string $fee_type
 * @property integer $total_fee
 * @property string $spbill_create_ip
 * @property string $time_start
 * @property string $time_expire
 * @property string $goods_tag
 * @property string $notify_url
 * @property string $trade_type
 * @property string $product_id
 * @property string $limit_pay
 * @property string $openid
 * @property string $params
 * @property string $create_time
 * @property integer $status
 */
class GoodsIapOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_iap_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_type', 'goods_type','product_id', 'quantity','transaction_id','purchase_date','app_item_id','create_time','goods_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'user_type' => Yii::t('app', '用户类型'),
            'goods_type' => Yii::t('app', '购买会员类型'),
            'goods_id'  => Yii::t('app', '购买会员类型'),
            'product_id' => Yii::t('app', 'product_id'),
            'quantity' => Yii::t('app', 'quantity'),
            'transaction_id' => Yii::t('app', 'transaction_id'),
            'purchase_date' => Yii::t('app', 'purchase_date'),
            'app_item_id' => Yii::t('app', 'app_item_id'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
}
