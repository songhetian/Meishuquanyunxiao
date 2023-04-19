<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "alipay_order".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $notify_time
 * @property string $notify_type
 * @property string $notify_id
 * @property string $sign_type
 * @property string $sign
 * @property string $out_trade_no
 * @property string $subject
 * @property integer $payment_type
 * @property string $trade_no
 * @property string $trade_status
 * @property string $seller_id
 * @property string $seller_email
 * @property string $buyer_id
 * @property string $buyer_email
 * @property double $total_fee
 * @property integer $quantity
 * @property double $price
 * @property string $body
 * @property string $gmt_create
 * @property string $gmt_payment
 * @property string $is_total_fee_adjust
 * @property string $use_coupon
 * @property double $discount
 * @property string $refund_status
 * @property string $gmt_refund
 * @property string $create_time
 * @property integer $status
 */
class AlipayOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'alipay_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // [['user_id', 'notify_type', 'notify_id', 'sign_type', 'sign', 'trade_no', 'trade_status', 'seller_id', 'seller_email', 'buyer_id', 'buyer_email', 'total_fee'], 'required','on' => ['default']],
            [['user_id', 'payment_type', 'quantity', 'status','vip_id'], 'integer'],
            [['notify_time', 'gmt_create', 'gmt_payment', 'gmt_refund', 'create_time'], 'safe'],
            [['total_fee', 'price', 'discount'], 'number'],
            [['notify_type', 'notify_id', 'sign_type', 'out_trade_no', 'trade_no'], 'string', 'max' => 64],
            [['sign'], 'string', 'max' => 3000],
            [['subject', 'seller_email', 'buyer_email','body'], 'string', 'max' => 128],
            [['trade_status', 'seller_id', 'buyer_id', 'is_total_fee_adjust', 'use_coupon', 'refund_status','user_type'], 'string', 'max' => 32],
            [['user_id', 'total_fee', 'subject', 'quantity'], 'safe', 'on' => ['background']],
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
            'notify_time' => Yii::t('app', '通知时间'),
            'notify_type' => Yii::t('app', '通知类型'),
            'notify_id' => Yii::t('app', '通知校验ID'),
            'sign_type' => Yii::t('app', '签名方式'),
            'sign' => Yii::t('app', '签名'),
            'out_trade_no' => Yii::t('app', '商户网站唯一订单号'),
            'subject' => Yii::t('app', '商品名称'),
            'payment_type' => Yii::t('app', '支付类型'),
            'trade_no' => Yii::t('app', '支付宝交易号'),
            'trade_status' => Yii::t('app', '交易状态'),
            'seller_id' => Yii::t('app', '卖家支付宝用户号'),
            'seller_email' => Yii::t('app', '卖家支付宝账号'),
            'buyer_id' => Yii::t('app', '买家支付宝用户号'),
            'buyer_email' => Yii::t('app', '买家支付宝账号'),
            'total_fee' => Yii::t('app', '交易金额'),
            'quantity' => Yii::t('app', '购买数量'),
            'price' => Yii::t('app', '商品单价'),
            'body' => Yii::t('app', '商品描述'),
            'gmt_create' => Yii::t('app', '交易创建时间'),
            'gmt_payment' => Yii::t('app', '交易付款时间'),
            'is_total_fee_adjust' => Yii::t('app', '是否调整总价'),
            'use_coupon' => Yii::t('app', '是否使用红包买家'),
            'discount' => Yii::t('app', '折扣'),
            'refund_status' => Yii::t('app', '退款状态'),
            'gmt_refund' => Yii::t('app', '退款时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
