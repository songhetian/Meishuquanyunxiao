<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%exam_review}}".
 *
 * @property integer $id
 * @property integer $exam_id
 * @property integer $review_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class ExamReview extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%exam_review}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['exam_id', 'review_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['exam_id', 'review_id', 'created_at', 'updated_at', 'status'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'exam_id' => Yii::t('app', '考试ID'),
            'review_id' => Yii::t('app', '阅卷人'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
