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
class GoodsWechatOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_wechat_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'appid', 'mch_id', 'nonce_str', 'sign', 'body', 'out_trade_no', 'total_fee', 'spbill_create_ip', 'notify_url', 'trade_type'], 'required','on' => ['default']],
            [['user_id', 'total_fee', 'status'], 'integer'],
            [['detail'], 'string'],
            [['time_start', 'time_expire', 'create_time'], 'safe'],
            [['appid', 'mch_id', 'device_info', 'nonce_str', 'sign', 'out_trade_no', 'goods_tag', 'product_id', 'limit_pay','user_type','goods_type'], 'string', 'max' => 32],
            [['body', 'attach', 'openid', 'params'], 'string', 'max' => 128],
            [['fee_type', 'spbill_create_ip', 'trade_type'], 'string', 'max' => 16],
            [['notify_url'], 'string', 'max' => 256],
            [['user_id', 'total_fee', 'body'], 'safe', 'on' => ['background']],
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
            'appid' => Yii::t('app', '公众账号ID'),
            'mch_id' => Yii::t('app', '商户号'),
            'device_info' => Yii::t('app', '设备号'),
            'nonce_str' => Yii::t('app', '随机字符串'),
            'sign' => Yii::t('app', '签名'),
            'body' => Yii::t('app', '商品描述'),
            'detail' => Yii::t('app', '商品详情'),
            'attach' => Yii::t('app', '附加数据'),
            'out_trade_no' => Yii::t('app', '商户订单号'),
            'fee_type' => Yii::t('app', '货币类型'),
            'total_fee' => Yii::t('app', '总金额'),
            'spbill_create_ip' => Yii::t('app', '终端IP'),
            'time_start' => Yii::t('app', '交易起始时间'),
            'time_expire' => Yii::t('app', '交易结束时间'),
            'goods_tag' => Yii::t('app', '商品标记'),
            'notify_url' => Yii::t('app', '通知地址'),
            'trade_type' => Yii::t('app', '交易类型'),
            'product_id' => Yii::t('app', '商品ID'),
            'limit_pay' => Yii::t('app', '指定支付方式'),
            'openid' => Yii::t('app', '用户标识'),
            'params' => Yii::t('app', '开通会员参数'),
            'create_time' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
