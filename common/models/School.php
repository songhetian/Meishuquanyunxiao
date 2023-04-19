<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%school}}".
 *
 * @property int $id
 * @property string $name 名称
 * @property int $pid 父id
 * @property string $depiction 简介
 * @property string $image 图片
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $status 状态
 */
class School extends \backend\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%school}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'pid', 'depiction', 'image', 'created_at', 'updated_at'], 'required'],
            [['pid', 'created_at', 'updated_at', 'status'], 'integer'],
            [['depiction'], 'string'],
            [['name', 'image'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'pid' => 'Pid',
            'depiction' => 'Depiction',
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
