<?php

namespace common\models;

use Yii;
use backend\models\Admin;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%activation_code}}".
 *
 * @property integer $id
 * @property string $code
 * @property integer $type
 * @property integer $relation_id
 * @property integer $studio_id
 * @property integer $status
 */
class ActivationCode extends ActiveRecord
{
    const USE_DELETED  = 20;
    const USE_ACTIVE   = 10;
    const TYPE_USER    = 2;
    const TYPE_TEACHER = 1;  
    const TYPE_FAMILY  = 3;  

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%activation_code}}';
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = time();
                $this->updated_at = time();
                /**
                 * 判断是否为183 否则三个月激活码时间为4月1或者5月1
                 */

                if($this->activetime == 0.25) {
                    if(in_array($this->studio_id,Yii::$app->params['Studio'])){
                        $this->due_time   = $this->getEndTimeDate($this->created_at,$this->activetime);
                    }else{
                        if(in_array($this->studio_id,Yii::$app->params['FreeThreeMonth'])) {
                            $this->due_time = "2021-06-11";
                            $this->is_first = 1;
                        }else{
                            $this->due_time = "2021-05-01";
                            $this->is_first = 1;
                        }
                    }
                }else{
                    $this->due_time   = $this->getEndTimeDate($this->created_at,$this->activetime);
                }
            }else{
                $this->updated_at = time();
            }
            return true;
        }
        return false;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {   
        return [
            [['code', 'type', 'studio_id','activetime','campus_id'], 'required','on'=>['create']],
            [['status'],'required','on'=>['update']],
            [['type', 'relation_id', 'studio_id', 'status','created_at','updated_at','is_active','activation_times'], 'integer'],
            ['activetime','double'],
            [['campus_id','class_id'], 'string'],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['activetime', 'in', 'range' => [0,1,2,3,0.019,0.09,0.25,0.5]],
            ['type', 'in', 'range' => [self::TYPE_TEACHER,self::TYPE_USER]],
            ['is_active', 'in', 'range' => [self::USE_DELETED, self::USE_ACTIVE]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['activation_times', 'default', 'value' => 1],
            [['code'], 'string', 'max' => 8],
            [['due_time'], 'string', 'max' => 30],
            ['is_first', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '验证码',
            'type' => '类型',
            'relation_id' => '关联 ID',
            'studio_id' => '校区 ID',
            'activetime' => '有效时间',
            'is_active'  => '激活状态',
            'status' => '状态',
        ];
    }

    public  function getEndTimeDate($time,$int) {
        $int = ($int == 0) ? 1 : $int;
        if($int >= 1){
            $year = floor($int);
            $EndTime =  strtotime("+$year years +1 day" ,$time);
        }elseif($int < 1 && $int >= 0.09){
            $month =  floor($int*12);
            $EndTime =  strtotime("+$month months +1 day",$time);
        }else{
            $days =  round($int*365)+1;
            $EndTime =  strtotime("+$days days",$time);
        }
        return date("Y-m-d",$EndTime);
    }
    /**
    *[获取激活码到期时间]
    *   
    */

    public static function GetUserEndTime($id, $type = 2) {

        $Code = self::findOne(['relation_id'=> $id , 'type' => $type]);

        $days = floor($Code->activetime * 365);

        return floor((strtotime("+ $days days",$Code->created_at) - time())/(24*3500));
    }

    /**
     * [多条获取作业]
     *
     *
     *
    */
    public function getAdmins()
    {
        return $this->hasOne(Admin::className(),['id'=>'relation_id'])->alias('admins');
    }
}
