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
 * This is the model class for table "works_list".
 *
 * @property integer $works_list_id
 * @property string $name
 * @property string $pic_url
 * @property integer $created_at
 * @property integer $update_at
 * @property integer $status
 * @property string $desc
 * @property string $type
 * @property integer $admin_id
 * @property integer $studio_id
 */
class WorksList extends ActiveRecord
{
    const IS_TEACHER_YES = 10;
    const IS_TEACHER_NO = 0;
    const TYPE_SUMIAO = 10;
    const TYPE_SECAI = 20;
    const TYPE_SHEJI = 30;
    const TYPE_SUXIE = 40;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'works_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'status', 'admin_id', 'studio_id','is_teacher'], 'integer'],
            [['desc'], 'string'],
            [['name', 'type'], 'string', 'max' => 255],
            [['pic_url'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'works_list_id' => '作品ID',
            'name' => '作品名',
            'pic_url' => '照片',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '状态',
            'desc' => '简介',
            'type' => '类型',
            'admin_id' => '创建人',
            'studio_id' => '校园ID',
            'is_teacher' => '作品所属',
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'status' => [
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),     
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
            ],
            'is_teacher' => [
                self::IS_TEACHER_YES => Yii::t('backend',  'Has Teacher'),     
                self::IS_TEACHER_NO => Yii::t('backend', 'Has Student'),
            ],
            'type' => [
                self::TYPE_SUMIAO => Yii::t('backend',  'Su Miao'),     
                self::TYPE_SECAI => Yii::t('backend', 'Se Cai'),
                self::TYPE_SHEJI => Yii::t('backend',  'She Ji'),     
                self::TYPE_SUXIE => Yii::t('backend', 'Su Xie'),
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
