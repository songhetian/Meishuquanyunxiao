<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property int $id
 * @property int $pid 学校id
 * @property string $depiction 简介
 * @property string $content 内容
 * @property int $time 时间
 * @property string $title 标题
 * @property int $classify_id 所属类别
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $status 状态
 */
class Article extends \backend\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pid', 'depiction', 'content', 'time', 'title', 'classify_id', 'created_at', 'updated_at'], 'required'],
            [['pid', 'time', 'classify_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['depiction', 'content'], 'string'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'Pid',
            'depiction' => 'Depiction',
            'content' => 'Content',
            'time' => 'Time',
            'title' => 'Title',
            'classify_id' => 'Classify ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
