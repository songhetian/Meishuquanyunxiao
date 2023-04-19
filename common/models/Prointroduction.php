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
 * This is the model class for table "new_list".
 *
 * @property integer $new_list_id
 * @property string $name
 * @property string $url
 * @property string $thumbnails
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $desc
 * @property integer $admin_id
 * @property integer $studio_id
 * @property integer $is_top
 * @property integer $is_banner
 */
class Prointroduction extends ActiveRecord
{
    const PUBLIC_NOT_BANNER = 0;
    const PUBLIC_IS_BANNER = 10;
    const PUBLIC_NOT_TOP = 0;
    const PUBLIC_IS_TOP = 10;
    const PUBLIC_NOT_PUSH = 1;
    const PUBLIC_IS_PUSH = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'prointroduction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'thumbnails', 'desc'], 'string'],
            [['created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_top', 'is_banner'], 'integer'],
            [['timing_push_time','is_push'],'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prointroduction_id' => 'ID',
            'name' => '标题',
            'url' => '外部链接',
            'thumbnails' => '封面缩略图(建议900像素 * 500像素)',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '状态',
            'desc' => '简介',
            'admin_id' => '创建人',
            'studio_id' => '机构id',
            'is_top' => '是否推荐',
            'is_banner' => '是否加入轮播图',
            'is_push' => '是否推送',
            'timing_push_time' => '定时推送时间',

        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'is_banner' => [
                self::PUBLIC_NOT_BANNER => Yii::t('backend',  'Not Banner'),      
                self::PUBLIC_IS_BANNER => Yii::t('backend', 'Has Banner'), 
            ],
            'is_top' => [
                self::PUBLIC_NOT_TOP => Yii::t('backend',  'Not TOP'),
                self::PUBLIC_IS_TOP => Yii::t('backend', 'Has TOP'),
            ],
            'status' => [
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),     
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
            ],
            'is_push' => [
                self::PUBLIC_NOT_PUSH => Yii::t('backend',  'Not PUSH'),
                self::PUBLIC_IS_PUSH => Yii::t('backend', 'Has PUSH'), 
            ]
        ];
        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
