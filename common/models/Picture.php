<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\models\Admin;
use backend\models\ActiveRecord;
use common\models\Campus;
use common\models\Category;
use common\models\Format;

/**
 * This is the model class for table "{{%picture}}".
 *
 * @property integer $id
 * @property integer $source
 * @property string $name
 * @property integer $metis_material_id
 * @property integer $publishing_company
 * @property integer $category_id
 * @property string $keyword_id
 * @property string $image
 * @property integer $watch_count
 * @property integer $admin_id
 * @property integer $is_public
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Picture extends ActiveRecord
{
    const SOURCE_LOCAL = 10;
    const SOURCE_METIS = 20;
    const PUBLIC_NOT_YET = 0;
    const PUBLIC_ED = 10;

    public $group;

    public static function tableName()
    {
        return '{{%picture}}';
    }

    public function beforeSave($insert)
    {
        //公共处理
        $admin_id = Yii::$app->user->identity->id;
        //$studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
        $this->keyword_id = (is_array($this->keyword_id)) ? Format::implodeValue($this->keyword_id) : NULL;
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                 $this->admin_id = $admin_id;
            }else{
                //if ($this->image && $this->image != $this->getOldAttribute('image')) {
                //  
                //  Oss::delFile('picture', 'image', $this->getOldAttribute('image'));
                //}else{
                    $this->image = $this->getOldAttribute('image');
                //}
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
           // [['category_id'], 'required'],
            //字段规范
            ['source', 'default', 'value' => self::SOURCE_LOCAL], 
            ['source', 'in', 'range' => [self::SOURCE_LOCAL, self::SOURCE_METIS]],
            ['is_public', 'default', 'value' => self::PUBLIC_ED], 
            ['is_public', 'in', 'range' => [self::PUBLIC_NOT_YET, self::PUBLIC_ED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['source', 'metis_material_id', 'publishing_company', 'category_id', 'watch_count', 'admin_id', 'is_public', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name'], 'string'],
            [['keyword_id'], 'safe'],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
                'maxFiles' => 100

            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'source' => Yii::t('app', '来源'),
            'name' => Yii::t('app', '名称'),
            'metis_material_id' => Yii::t('app', '美术圈素材ID'),
            'publishing_company' => Yii::t('app', '出版社'),
            'group' => Yii::t('app', '所属文件夹'),
            'category_id' => Yii::t('app', '分类'),
            'keyword_id' => Yii::t('app', '关键字'),
            'image' => Yii::t('app', '图片'),
            'watch_count' => Yii::t('app', '查看次数'),
            'admin_id' => Yii::t('app', '上传者'),
            'is_public' => Yii::t('app', '是否公开'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'source' => [
                self::SOURCE_LOCAL => Yii::t('common', 'Local'),
                self::SOURCE_METIS => Yii::t('common', 'Metis'),
            ],
            'is_public' => [
                self::PUBLIC_NOT_YET => Yii::t('common', 'Not Publiced'),
                self::PUBLIC_ED => Yii::t('common', 'Publiced'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }
    
    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
