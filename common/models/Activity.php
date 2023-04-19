<?php

namespace common\models;
use backend\models\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "{{%activity}}".
 *
 * @property int $id
 * @property int $type 类型
 * @property string $title 标题
 * @property string $image 图片
 * @property string $url 链接地址
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 */
class Activity extends ActiveRecord
{
    const TOP = 10;
    const NOT_TOP = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'created_at', 'updated_at', 'is_top','status','turn_type','turn_id'], 'integer'],
            [['type'], 'required'],
            [['title', 'image'], 'string', 'max' => 100],
            ['image','required','on'=>['create','nei','wai']],
            [['turn_type','turn_id'],'required','on'=>['nei']],
            [['url'],'required','on'=>['wai']],
            [['url'], 'string', 'max' => 150],
            ['is_top', 'default', 'value' => self::NOT_TOP],
            ['is_top', 'in', 'range' => [self::NOT_TOP, self::TOP]],
            ['turn_type', 'in', 'range' => [1,2,3,4,5]]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'title' => '标题',
            'image' => '活动图',
            'url' => '链接地址',
            'is_top' => '置顶',
            'turn_id'=> '跳转id',
            'turn_type'=> '跳转类型',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'status' => '状态',
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'type' => [
                10 =>  '内部跳转',
                20 =>  '外部跳转',                
            ],
            'is_top' => [
                10 =>  '置顶',
                0 =>  '非置顶', 
            ],
            'turn_type' => [
                1  => '邀请好友',
                2  => '视频',
                3  => '电子书',
                4  => '云课件'
            ],
        ];
        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }










}
