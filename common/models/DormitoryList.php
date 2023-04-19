<?php

namespace common\models;

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

use Yii;

/**
 * This is the model class for table "dormitory_list".
 *
 * @property integer $dormitory_list_id
 * @property string $pic_url
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $name
 * @property integer $admin_id
 * @property integer $studio_id
 * @property integer $bed_num
 */
class DormitoryList extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dormitory_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'bed_num'], 'integer'],
            [['pic_url', 'name'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dormitory_list_id' => '宿舍ID',
            'pic_url' => '封面',
            'created_at' => '创建时间',
            'updated_at' => '最近更新时间',
            'status' => '状态',
            'name' => '宿舍名',
            'admin_id' => '创建人',
            'studio_id' => 'Studio ID',
            'bed_num' => '床位数',
        ];
    }
}
