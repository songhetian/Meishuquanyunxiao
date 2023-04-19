<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

class InviteSell  extends  \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invite_sell}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'price','status'], 'integer'],
            [['user_type','buy_type','name','goods_type'], 'string', 'max' => 100],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '购买用户id',
            'user_type' => '用户类型',
            'buy_type' => '购买类型',
            'goods_type' => '商品类型',
            'order_id' => '订单id',
            'price' => '价格',
            'time' => '购买时间',
            'status' => '状态',
            'name' => '商品名称',
        ];
    }
    
}
