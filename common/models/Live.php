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
 * This is the model class for table "live".
 *
 * @property integer $id
 * @property string $live_id
 * @property string $start_time
 * @property string $end_time
 * @property integer $cc_id
 * @property integer $user_id
 * @property integer $status
 * @property integer $connections
 * @property integer $is_phone
 * @property string $title
 * @property string $description
 * @property string $pic_url
 * @property integer $play_status
 */
class Live extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['live_id'], 'required'],
            [['user_id', 'status', 'connections', 'play_status','is_sideways','course_id'], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['live_id', 'title','cc_id','play_type'], 'string', 'max' => 200],
            [['description', 'pic_url','tosee'], 'string', 'max' => 300]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'live_id' => Yii::t('app', '直播ID'),
            'start_time' => Yii::t('app', '开始时间'),
            'end_time' => Yii::t('app', '结束时间'),
            'cc_id' => Yii::t('app', '直播间ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'status' => Yii::t('app', '状态'),
            'connections' => Yii::t('app', '观看人数'),
            'play_type' => Yii::t('app', '直播类型'),
            'title' => Yii::t('app', '标题'),
            'description' => Yii::t('app', '简介'),
            'pic_url' => Yii::t('app', '缩略图'),
            'is_sideways' => Yii::t('app', '是否是横屏直播'),
            'play_status' => Yii::t('app', '播放状态'),
            'course_id' => Yii::t('app', '课件id'),
        ];
    }
}
