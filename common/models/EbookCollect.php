<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "ebook_collect".
 *
 * @property integer $id
 * @property integer $ebook_id
 * @property integer $category_id
 * @property integer $admin_id
 * @property string $name
 * @property integer $is_public
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class EbookCollect extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ebook_collect';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ebook_id', 'admin_id','studio_id','role'], 'required'],

            [['ebook_id', 'category_id', 'admin_id', 'is_public', 'created_at', 'updated_at', 'status','role','studio_id','is_top','book_type'], 'integer'],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],

            [['pic_url','publishing_company'], 'string'],

            ['is_top', 'default', 'value' => 0],

            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],

            ['book_type', 'in', 'range' => [0,1]],

            ['role', 'in', 'range' => [10,20,30]],

            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'ebook_id'    => '电子书ID',
            'category_id' => '分类',
            'admin_id'    => '收藏者',
            'name'        => '电子书名',
            'role'        => '身份',
            'studio_id'   => '画室id',
            'is_top'      => '是否置顶',
            'is_public'   => '是否公开',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
            'status'      => 'Status',
        ];
    }
}
