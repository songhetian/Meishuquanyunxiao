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
 * This is the model class for table "teacher_list".
 *
 * @property integer $teacher_list_id
 * @property string $name
 * @property string $pic_url
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $desc
 * @property integer $admin_id
 * @property integer $studio_id
 */
class TeacherList extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'teacher_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['teacher_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id'], 'integer'],
            [['desc'], 'string'],
            [['name','auth'], 'string', 'max' => 255],
            [['pic_url'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'teacher_list_id' => '名师ID',
            'name' => '教师姓名',
            'pic_url' => '照片',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'status' => '状态',
            'desc' => '介绍',
            'admin_id' => '作者ID',
            'studio_id' => '校园ID',
            'auth' => '职位',
        ];
    }
    public static function getValues($field, $value = false)
    {
        $values = [
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
