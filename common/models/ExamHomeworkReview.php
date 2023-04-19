<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%exam_homework_review}}".
 *
 * @property integer $id
 * @property integer $homework_id
 * @property integer $review_id
 * @property double $score
 * @property integer $review_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class ExamHomeworkReview extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%exam_homework_review}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['homework_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['homework_id', 'review_id', 'review_at', 'created_at', 'updated_at', 'status'], 'integer'],
            [['score'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'homework_id' => Yii::t('app', '作业ID'),
            'review_id' => Yii::t('app', '阅卷人'),
            'score' => Yii::t('app', '得分'),
            'review_at' => Yii::t('app', '阅卷时间'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}