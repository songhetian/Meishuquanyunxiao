<?php

namespace common\models;
use Yii;
use common\models\Classes;
use backend\models\Admin;
use common\models\Format;
use yii\helpers\ArrayHelper;
use backend\models\ActiveRecord;
/**
 * This is the model class for table "{{%classes_give}}".
 *
 * @property int $id
 * @property int $values 赠送数值
 * @property varcher $title 标题
 * @property varcher $type 赠送类型
 * @property int $created_at 创建时间
 * @property int $status 状态
 */
class ClassesGive extends ActiveRecord
{
    public $code;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%classes_give}}';
    }
   
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['values', 'created_at', 'status'], 'integer'],
            [['values','title','type'],'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'values'   => '赠送数值',
            'title' => '标题',
            'type' => '赠送类型',
            'created_at' => '创建时间',
            'status' => '状态',
        ];
    }
}
