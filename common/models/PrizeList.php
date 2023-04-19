<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\models\Admin;
use backend\models\ActiveRecord;
use common\models\Campus;
use common\models\Group;
use common\models\Query;
use common\models\Format;
use common\models\Curl;
use components\Oss;
/**
 * This is the model class for table "prize_list".
 *
 * @property integer $prize_list_id
 * @property string $name
 * @property string $url
 * @property string $thumbnails
 * @property string $studio_name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $desc
 * @property integer $admin_id
 * @property integer $studio_id
 * @property integer $is_banner
 */
class PrizeList extends ActiveRecord
{
    const PUBLIC_NOT_BANNER = 0;
    const PUBLIC_IS_BANNER = 10;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'prize_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['thumbnails', 'desc'], 'string'],
            [['created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_banner'], 'integer'],
            [['name', 'url'], 'string', 'max' => 500],
            [['studio_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prize_list_id' => '辉煌成绩ID',
            'name' => '标题',
            'url' => '外部链接（没有不填）',
            'thumbnails' => '* 封面',
            'studio_name' => '学校名称',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '状态',
            'desc' => '内容简介',
            'admin_id' => '创建人ID',
            'studio_id' => '校园ID',
            'is_banner' => '是否设置轮播图',
        ];
    }
    public static function getValues($field, $value = false)
    {
        $values = [
            'is_banner' => [
                self::PUBLIC_NOT_BANNER => Yii::t('backend',  'Not Banner'),      
                self::PUBLIC_IS_BANNER => Yii::t('backend', 'Has Banner'), 
            ],
            'status' => [
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),     
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
