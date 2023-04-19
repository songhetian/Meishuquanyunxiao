<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;
use common\models\Campus;
use backend\models\Admin;
use common\models\Query;

/**
 * This is the model class for table "{{%classes}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $year
 * @property integer $campus_id
 * @property integer $supervisor
 * @property string $note
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Classes extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%classes}}';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
    	return [
    		['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            [['name', 'year', 'campus_id', 'supervisor'], 'required'],
            //新增场景
            [['price', 'subtitle','class_type','class_time','class_content','discount'], 'required','on'=>['create']],
            [['year', 'campus_id', 'supervisor', 'created_at', 'price','updated_at', 'type','status','tip','assistant','lecturer'], 'integer'],
            [['note','image','subtitle','class_type','class_time','class_content','discount'], 'string'],
            [['name'], 'string', 'max' => 32],
            ['original_price','double'],
            ['original_price', 'default', 'value' => 0.00],
            ['price', 'default', 'value' => 0],
            ['type',  'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'name'          => Yii::t('app', '名称'),
            'year'          => Yii::t('app', '所属年份'),
            'campus_id'     => Yii::t('app', '所属校区'),
            'supervisor'    => Yii::t('app', '班主任'),
            'note'          => Yii::t('app', '备注'),
            'price'         => '价格',
            'image'         => '缩略图',
            'title'         => '标题',
            'subtitle'      => '副标题',
            'class_type'    => '上课形式',
            'class_time'    => '上课时间',
            'class_content' => '上课内容',
            'lecturer'      => '讲师',
            'assistant'     => '助教',
            'discount'      => '优惠内容',
            'type'          => '课程类型',
            'tip'           => '优惠备注',
            'created_at'    => Yii::t('app', '创建时间'),
            'updated_at'    => Yii::t('app', '更新时间'),
            'status'        => Yii::t('app', '状态'),
        ];
    }

    public static function getEditorList($class_id) {

        $list =   self::find()
                      ->select("supervisor,assistant,lecturer")
                      ->where(['id'=>$class_id])
                      ->asArray()
                      ->one();
        return array_values(array_filter($list));
    }

    static public function getYearList()
    {
        $year = date('Y');
        
        $res[$year] = $year;

        for ($i = 2000; $i <= 2040; $i++) { 
            if($i != $year){
                $res[$i] = $i;
            }
        }
        return $res;
    }

    public static function getClassesList($campus_id = NULL)
    {
        if(!$campus_id){
            $campus_id = Yii::$app->user->identity->campus_id;
        }
        $class_id = Yii::$app->user->identity->class_id;
        $model = static::find()
            ->andFilterWhere(['campus_id' => Format::explodeValue($campus_id)])
            ->andFilterWhere(['id' => Format::explodeValue($class_id)])
            ->andFilterWhere(['status' => self::STATUS_ACTIVE])
            ->all();
        foreach ($model as $v) {
            $res[$v->campuses->name][$v->id] = $v->year . '　—　' . $v->name;
        }
        
        return ($res) ? $res : [];
    }
    public function getTips()
    {
        return $this->hasOne(ClassesGive::className(), ['id' => 'tip'])->alias('tips');
    }

    public function getCampuses()
    {
        return $this->hasOne(Campus::className(), ['id' => 'campus_id'])->alias('campuses');
    }

    public function getStudents()
    {
        return $this->hasMany(User::className(), ['class_id' => 'id'])->alias('students');
    }
    
    public function getSupervisors()
    {
        return $this->hasOne(Admin::className(), ['id' => 'supervisor'])->alias('supervisors');
    }
}