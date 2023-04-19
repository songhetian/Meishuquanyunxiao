<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use common\models\ClassPeriod;
use common\models\Classes;
use common\models\Category;
use backend\models\Admin;
use common\models\InstructionMethod;
use common\models\CourseMaterial;
/**
 * This is the model class for table "{{%course}}".
 *
 * @property integer $id
 * @property integer $class_period_id
 * @property integer $class_id
 * @property integer $category_id
 * @property integer $instructor
 * @property integer $instruction_method_id
 * @property integer $course_material_id
 * @property integer $started_at
 * @property integer $ended_at
 * @property string $class_content
 * @property string $class_emphasis
 * @property string $note
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Course extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%course}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->started_at = (!is_int($this->started_at)) ? strtotime($this->started_at) : $this->started_at;
            $this->ended_at = (!is_int($this->ended_at)) ? strtotime($this->ended_at) : $this->ended_at;
            if ($this->isNewRecord) {
                $this->admin_id = Yii::$app->user->identity->id;
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

    public function rules()
    {
        return [
            //特殊需求
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'started_at', 'ended_at'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'course_material_id', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['class_content', 'class_emphasis', 'note'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'class_period_id' => Yii::t('app', '上课时间'),
            'class_id' => Yii::t('app', '所属班级'),
            'category_id' => Yii::t('app', '科目'),
            'instructor' => Yii::t('app', '教学老师'),
            'instruction_method_id' => Yii::t('app', '教学形式'),
            'course_material_id' => Yii::t('app', '选择课件'),
            'started_at' => Yii::t('app', '开始时间'),
            'ended_at' => Yii::t('app', '结束时间'),
            'class_content' => Yii::t('app', '教学内容'),
            'class_emphasis' => Yii::t('app', '教学重点'),
            'note' => Yii::t('app', '备注'),
            'admin_id' => Yii::t('app', '上传者'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    static public function getDisabledDates($class_period_id, $class_id, $id = 0)
    {
        $res = [];
        if($class_period_id && $class_id){
            $query =  static::find()->andFilterWhere(['class_period_id' => $class_period_id, 'class_id' => $class_id , 'status' => self::STATUS_ACTIVE]);
            if($id){
                $query->andFilterWhere(['!=', 'id', $id]);
            }
            $model = $query->all();
            if($model){
                foreach ($model as $v) {
                    $res = self::prDates($res, $v->started_at, $v->ended_at);
                }
            }
        }
        return ($res) ? $res : ['1900/01/01'];
    }

    static public function getMaxDate($class_period_id, $class_id, $started_at, $id = 0)
    {
        if($class_period_id && $class_id && $started_at){  
            $query = static::find()->andFilterWhere(['class_period_id' => $class_period_id, 'class_id' => $class_id , 'status' => self::STATUS_ACTIVE]);
            if($id){
                $query->andFilterWhere(['!=', 'id', $id]);
            }
            $min = $query->andFilterWhere(['>', 'started_at', $started_at])->min('started_at');
            $max = ($min) ? date('Y/m/d', strtotime('-1 day', $min)) : '2040/12/31';
            return $max;
        }
    }

    /**
     * 求两个日期之间的所有日期
     * @param string $started_at
     * @param string $ended_at
     */
    static public function prDates($res, $started_at, $ended_at){
        while ($started_at <= $ended_at){
            $res[] = date('Y/m/d', $started_at);
            $started_at = strtotime('+1 day', $started_at);
        }
        return $res;
    }

    //判断时间类型(日,月,年)
    static public function getDateType($query, $started_at)
    {
        $tableName = Course::tableName();
        $date = explode('/', $started_at);
        if (count($date) == 3) {
            $started_at = strtotime($started_at);
            $query->andFilterWhere(['<=', $tableName . '.started_at', $started_at])
                  ->andFilterWhere(['>=', $tableName . '.ended_at', $started_at])
                  ->orderBy($tableName . '.class_period_id,' . $tableName . '.id');
        } else if (count($date) == 2) {
            $time = self::getMouth($date);
            $query->andFilterWhere(['>=', $tableName . '.started_at', $time['started_at']])
                  ->andFilterWhere(['<=', $tableName . '.ended_at', $time['ended_at']])
                  ->orderBy($tableName . '.started_at, ' . $tableName . '.class_period_id, ' . $tableName . '.id');
        }
        return $query;
    }

    //获取月初和月末的时间戳
    static public function getMouth($date)
    {
        $startDay = $date[0] . '-' . $date[1] . '-1';
        $endDay = $date[0] . '-' . $date[1] . '-' . date('t', strtotime($startDay));
        return [
            'started_at' => strtotime($startDay),
            'ended_at' => strtotime($endDay)
        ];
    }

    public function getClassPeriods()
    {
        return $this->hasOne(ClassPeriod::className(), ['id' => 'class_period_id'])->alias('class_periods');
    }

    public function getClasses()
    {
        return $this->hasOne(Classes::className(), ['id' => 'class_id'])->alias('classes');
    }

    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }

    public function getInstructors()
    {
        return $this->hasOne(Admin::className(), ['id' => 'instructor'])->alias('instructors');
    }

    public function getInstructionMethods()
    {
        return $this->hasOne(InstructionMethod::className(), ['id' => 'instruction_method_id'])->alias('instruction_methods');
    }

    public function getCourseMaterials()
    {
        return $this->hasOne(CourseMaterial::className(), ['id' => 'course_material_id'])->alias('course_materials');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}