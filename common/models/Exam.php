<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "exam".
 *
 * @property integer $id
 * @property string $title
 * @property integer $type
 * @property integer $time
 * @property integer $category_id
 * @property string $class_id
 * @property string $content
 * @property string $image
 * @property integer $length
 * @property string $require
 * @property integer $release_state
 * @property integer $review_state
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Exam extends ActiveRecord
{
    public $review_id;

    const TYPE_TITLE = 10;
    const TYPE_BASIC = 20;
    const TYPE_WEEK = 30;
    const TYPE_MONTH = 40;

    const RELEASE_STATE_NOT_YET = 0;
    const RELEASE_STATE_ED = 10;

    const REVIEW_STATE_NOT_YET = 0;
    const REVIEW_STATE_ED = 10;

    public static function tableName()
    {
        return '{{%exam}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->time = strtotime($this->time);
                if($this->image){
                    foreach ($this->image as $url) {
                        $exps = explode('?', urldecode($url));
                        $name = explode('/', $exps[0]);
                        $arr[] = end($name);
                    }
                    $this->image = Format::implodeValue($arr);
                }
            }else{
                if(!is_numeric($this->time)){
                    $this->time = strtotime($this->time);
                }
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //创建对应阅卷人数据
        if($insert) {
            $enc = json_encode($this->review_id);
            $res = json_decode($enc);
            //$res = json_decode($this->review_id);
            if($res){
                foreach ($res as $v) {
                    $review = new ExamReview();
                    $review->exam_id = $this->id;
                    $review->review_id = $v->review_id;
                    $review->save();
                }
            }
        }
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            ['review_id', 'required', 'on' => 'create'],
            [['title', 'type', 'time', 'category_id', 'class_id', 'content', 'length', 'admin_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['type', 'category_id', 'length', 'release_state', 'review_state', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['content', 'require'], 'string'],
            [['title'], 'string', 'max' => 32],
            [['class_id'], 'string', 'max' => 100],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', '题目'),
            'type' => Yii::t('app', '类型'),
            'time' => Yii::t('app', '考试时间'),
            'category_id' => Yii::t('app', '考试科目'),
            'class_id' => Yii::t('app', '参与班级'),
            'content' => Yii::t('app', '内容'),
            'image' => Yii::t('app', '图片'),
            'length' => Yii::t('app', '考试时长'),
            'require' => Yii::t('app', '要求'),
            'release_state' => Yii::t('app', '发布状态'),
            'review_state' => Yii::t('app', '阅卷状态'),
            'admin_id' => Yii::t('app', '创建人'),
            'review_id' => Yii::t('app', '阅卷人'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'type' => [
                self::TYPE_TITLE => Yii::t('backend', 'Title'),
                self::TYPE_BASIC => Yii::t('backend', 'Basic'),
                self::TYPE_WEEK => Yii::t('backend', 'Week'),
                self::TYPE_MONTH => Yii::t('backend', 'Month'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ]
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getClasses()
    {
        return $this->hasOne(Classes::className(), ['id' => 'class_id'])->alias('classes');
    }

    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }
}
