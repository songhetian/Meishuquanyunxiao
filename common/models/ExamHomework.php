<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%exam_homework}}".
 *
 * @property integer $id
 * @property integer $exam_id
 * @property integer $user_id
 * @property string $image
 * @property double $score
 * @property integer $review_state
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class ExamHomework extends ActiveRecord
{
    const REVIEW_STATE_NOT_YET = 0;
    const REVIEW_STATE_ED = 10;

    public static function tableName()
    {
        return '{{%exam_homework}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['exam_id', 'user_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['exam_id', 'user_id', 'review_state', 'created_at', 'updated_at', 'status'], 'integer'],
            [['score'], 'number'],
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
            'exam_id' => Yii::t('app', '考试ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'image' => Yii::t('app', '作业'),
            'score' => Yii::t('app', '得分'),
            'review_state' => Yii::t('app', '阅卷状态'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'review_state' => [
                self::REVIEW_STATE_NOT_YET => Yii::t('backend', 'Not Review'),
                self::REVIEW_STATE_ED => Yii::t('backend', 'Has Review'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ]
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }
}