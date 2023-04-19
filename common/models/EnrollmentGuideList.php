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
 * This is the model class for table "enrollment_guide_list".
 *
 * @property integer $enrollment_guide_list_id
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
class EnrollmentGuideList extends ActiveRecord
{
    const PUBLIC_NOT_BANNER = 0;
    const PUBLIC_IS_BANNER = 10;
    const PUBLIC_NOT_TOP = 0;
    const PUBLIC_IS_TOP = 10;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'enrollment_guide_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_top', 'is_banner'], 'integer'],
            [['desc'], 'string'],
            [['name', 'url'], 'string', 'max' => 255],
            [['thumbnails'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'enrollment_guide_list_id' => '招生简章ID',
            'name' => '标题',
            'url' => '外部链接（没有不填）',
            'thumbnails' => '封面',
            'created_at' => '创建时间',
            'updated_at' => '最后修改时间',
            'status' => '状态',
            'desc' => '内容',
            'admin_id' => '创建人',
            'studio_id' => '所属学校',
            'is_top' => '是否推荐',
            'is_banner' => '是否加入轮播图',
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
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }
        public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
