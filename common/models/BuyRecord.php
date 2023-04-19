<?php

namespace common\models;
use Yii;
use common\models\Gather;
use backend\models\Admin;
use common\models\Format;
use yii\helpers\ArrayHelper;
use backend\models\ActiveRecord;
/**
 * This is the model class for table "{{%buy_record}}".
 *
 * @property int $id
 * @property int $buy_id 购买者id
 * @property int $buy_studio 购买者画室
 * @property int $gather_id 云课件id
 * @property int $gather_studio 云课件画室
 * @property int $created_at 购买时间
 * @property int $updated_at 修改时间
 * @property int $active_at 过期时间
 * @property string $price 价格
 * @property int $status 状态
 */
class BuyRecord extends ActiveRecord
{
    public $code;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%buy_record}}';
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->admin_id      = Yii::$app->user->identity->id;
                $this->gather_studio = Campus::findOne(Yii::$app->user->identity->campus_id)->studio_id;
            }else{

            }
            return true;
        }
        return false;
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buy_id', 'buy_studio', 'gather_id', 'gather_studio', 'created_at', 'updated_at', 'active_at', 'status','admin_id'], 'integer'],
            ['role','in','range'=>[10,20]],
            ['code','string','length' => [8, 11]],
            [['buy_id','gather_id','role','price','buy_studio'],'required'],
            [['price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code'   => '购买者',
            'buy_id' => '购买者',
            'buy_studio' => '购买画室',
            'gather_id' => '课件包',
            'gather_studio' => '课件包画室',
            'created_at' => '购买时间',
            'updated_at' => '修改时间',
            'active_at' => '过期时间',
            'price' => '价格',
            'role'  => '身份',
            'status' => '状态',
        ];
    }
    public static function getValues($field, $value = false)
    {
        $values = [
            'role' => [
                10 =>  '老师',
                20 =>  '学生',                
            ],
        ];
        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }
    public static function getGatherList()
    {
        $studio_id = Campus::findOne(Yii::$app->user->identity->campus_id)->studio_id;

        $model = Gather::find()
                 ->select('id,name')
                 ->where(['studio_id'=>$studio_id,'status'=>Gather::STATUS_ACTIVE])
                 ->all();
                 
        return ArrayHelper::map($model, 'id', 'name');
    }

    //处理购买
    public  function HandleBuy()
    {
        switch ($this->role) {
            case 10:
                if(strlen($this->code) == 8) { 
                   $this->buy_id      =  ActivationCode::findOne(['code'=>$this->code,'type'=>1,'studio_id'=>$this->buy_studio])->relation_id;
                }else if(strlen($this->code) == 11) {
                   $this->buy_id  =  Admin::findOne(['phone_number'=>$this->code,'status'=>10,'studio_id'=>$this->buy_studio])->id;
                }
                break;
            case 20:
                if(strlen($this->code) == 8) { 
                   $this->buy_id      =  ActivationCode::findOne(['code'=>$this->code,'type'=>2,'studio_id'=>$this->buy_studio])->relation_id;
                }else if(strlen($this->code) == 11) {
                   $this->buy_id      =  User::findOne(['phone_number'=>$this->code,'studio_id'=>$this->buy_studio,'status'=>10])->id;
                }
                break;           
        }
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'buy_id'])->alias('admins');
    }
    public function getStudents()
    {
        return $this->hasOne(User::className(), ['id' => 'buy_id'])->alias('students');
    }
    public function getStudios()
    {
        return $this->hasOne(Studio::className(), ['id' => 'buy_studio'])->alias('studios');
    }
    public function getGathers()
    {
        return $this->hasOne(Gather::className(), ['id' => 'gather_id'])->alias('gathers');
    }
}
