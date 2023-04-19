<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%common_problem}}".
 *
 * @property int $id id
 * @property string $title 标题
 * @property string $info 简介
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $status 状态
 */
class CommonProblem extends \backend\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%common_problem}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'info'], 'required'],
            [['info'], 'string'],
            [['created_at', 'updated_at', 'status'], 'integer'],
            [['title'], 'string', 'max' => 100],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'info' => '内容',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'status' => '状态',
        ];
    }
}
