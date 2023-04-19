<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property integer $id
 * @property string $title
 * @property string $type
 * @property string $icon
 * @property string $url
 * @property string $name
 * @property integer $created_at
 * @property integer $status
 */
class News extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'status','isArtWorld'], 'integer'],
            [['title', 'type','msg'], 'string', 'max' => 255],
            [['icon', 'url'], 'string', 'max' => 500],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'type' => '类型',
            'icon' => '图标',
            'url' => '链接地址',
            'name' => '标识',
            'created_at' => '创建时间',
            'status' => '状态',
            'isArtWorld' => '是否不能进入',
            'msg' => '消息',
        ];
    }
}
