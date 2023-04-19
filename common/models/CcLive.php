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
 * This is the model class for table "cc_live".
 *
 * @property integer $id
 * @property integer $cc_id
 * @property string $title
 * @property string $description
 * @property integer $user_id
 * @property integer $templatetype
 * @property integer $authtype
 * @property string $publisherpass
 * @property string $assistantpass
 * @property integer $playpass
 * @property string $checkurl
 * @property integer $barrage
 * @property integer $foreignpublish
 * @property integer $openlowdelaymode
 * @property integer $showusercount
 * @property string $create_time
 * @property integer $status
 * @property integer $is_recommend
 * @property string $start_time
 * @property string $publish_url
 * @property string $pic_url
 */
class CcLive extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cc_live';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'templatetype','play_status', 'authtype', 'playpass', 'barrage', 'foreignpublish', 'openlowdelaymode', 'showusercount', 'status', 'is_recommend','tactics_id','is_sideways','studio_id','course_id'], 'integer'],
            [['user_id'], 'required'],
            [['create_time', 'start_time','end_time','cclive_type','tosee'], 'safe'],
            [['title', 'description', 'publisherpass', 'assistantpass', 'cc_id', 'checkurl', 'publish_url', 'pic_url','play_type'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cc_id' => Yii::t('app', 'CC直播间ID'),
            'title' => Yii::t('app', '直播间名字'),
            'description' => Yii::t('app', '直播间描述'),
            'user_id' => Yii::t('app', '用户ID'),
            'templatetype' => Yii::t('app', '直播间类型'),
            'authtype' => Yii::t('app', '验证方式'),
            'publisherpass' => Yii::t('app', '推流端密码，即讲师密码'),
            'assistantpass' => Yii::t('app', '助教端密码'),
            'playpass' => Yii::t('app', '播放端密码'),
            'checkurl' => Yii::t('app', '验证地址'),
            'barrage' => Yii::t('app', '是否开启弹幕'),
            'foreignpublish' => Yii::t('app', '是否开启第三方推流'),
            'openlowdelaymode' => Yii::t('app', '开启直播低延时模式'),
            'showusercount' => Yii::t('app', '在页面显示当前在线人数'),
            'create_time' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '状态'),
            'is_recommend' => Yii::t('app', '推荐状态'),
            'start_time' => Yii::t('app', '开始直播时间'),
            'end_time' => Yii::t('app', '结束直播时间'),
            'publish_url' => Yii::t('app', '推流地址'),
            'pic_url' => Yii::t('app', '封面'),
            'studio_id' => Yii::t('app', ''),
            'play_status' => Yii::t('app', '直播状态'),
            'play_type' => Yii::t('app', '直播类型'),
            'is_sideways' => Yii::t('app', '是否为横屏直播'),
            'tactics_id' => Yii::t('app', '策略'),
            'is_bespoke' =>  Yii::t('app', '是否为预告'),
            'cclive_type' =>  Yii::t('app', '直播类型'),
            'course_id' => Yii::t('app', '课件id'),
        ];
    }
}