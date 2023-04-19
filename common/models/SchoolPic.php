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
 * This is the model class for table "school_pic".
 *
 * @property integer $school_pic_id
 * @property string $pic_url
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $desc
 * @property string $type
 * @property integer $admin_id
 * @property integer $studio_id
 */
class SchoolPic extends ActiveRecord
{
    const TYPE_XIAOYUAN = 10;
    const TYPE_JIAOSHI = 20;
    const TYPE_SHITANG = 30;
    const TYPE_SUSHE = 40;
    const TYPE_QITA = 50;
    const TYPE_CHAOSHI = 60;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'school_pic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['school_pic_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id'], 'integer'],
            [['desc'], 'string'],
            [['pic_url', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'school_pic_id' => '照片ID',
            'pic_url' => '照片',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '状态',
            'desc' => '简介',
            'type' => '类型',
            'admin_id' => '创建人id',
            'studio_id' => '学校id',
        ];
    }
        public static function getValues($field, $value = false)
    {
        $values = [
            'status' => [
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),     
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
            ],
            'type' => [
                self::TYPE_XIAOYUAN => Yii::t('backend',  'Xiao Yuan'),     
                self::TYPE_JIAOSHI => Yii::t('backend', 'Jiao Shi'),
                self::TYPE_SHITANG => Yii::t('backend',  'Shi Tang'),     
                self::TYPE_SUSHE => Yii::t('backend', 'Su She'),
                self::TYPE_QITA => Yii::t('backend', 'Qi Ta'),
                self::TYPE_CHAOSHI => Yii::t('backend', 'Chao Shi'),
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
