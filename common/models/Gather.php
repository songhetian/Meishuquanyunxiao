<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use backend\models\Admin;
use common\models\CourseMaterial;
use common\models\Category;
use common\models\Format;

/**
 * This is the model class for table "{{%gather}}".
 *
 * @property int $id
 * @property string $name 包名称
 * @property int $category_id 课件分类
 * @property string $course_material_id 关联教案
 * @property int $activetime 有效时长
 * @property double $price 价格
 * @property string $image 简介图片
 * @property string $author 教案作者
 * @property int $admin_id 制作人
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property string $introduction 简介
 * @property int $status 状态
 */
class Gather extends ActiveRecord
{

    const NOT_PUBLIC = 0;
    const YES_PUBLIC = 10;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gather}}';
    }

    public function beforeSave($insert)
    {
        $this->course_material_info = self::concatMaterial(Format::implodeValue($this->course_material_id));
        //公共处理
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->course_material_id =  Format::implodeValue($this->course_material_id);
                $this->admin_id = Yii::$app->user->identity->id;
                $this->created_at = time();
                $this->updated_at = time();
            }else{
                $this->course_material_id =  Format::implodeValue($this->course_material_id);
                $this->updated_at = time();
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'category_id', 'activetime', 'price','introduction','phone_number','author','course_material_id','is_public'], 'required'],
            ['image','required','on'=>['create']],
            [['category_id', 'activetime', 'admin_id', 'created_at', 'updated_at','author','watch_number','is_public'], 'integer'],
            [['phone_number'], 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/', 'message'=>'请填写有效手机号。'],
            [['price'], 'number'],
            [['phone_number'], 'string', 'length' => 11],
            [['introduction','image'], 'string'],
            [['status','course_material_id'],'safe'],
            [['name'], 'string', 'max' => 100],
            ['is_public', 'default', 'value' => 10],
            #[['course_material_info'], 'string', 'max' => 200],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'   => 'ID',
            'name' => '包名称',
            'category_id'        => '课件分类',
            'course_material_id' => '关联课件',
            'activetime' => '有效时长(年)',
            'price'      => '价格',
            'author'     => '教案作者',
            'admin_id'   => '制作人',
            'phone_number' => '联系电话',
            'watch_number' => '浏览次数',
            'course_material_info' => '教案文字详情',
            'image'        => '图片',
            'is_public'    => '是否公开',
            'created_at'   => '创建时间',
            'updated_at'   => '修改时间',
            'introduction' => '简介',
            'status'       => '状态',
        ];
    }


    public static function getValues($field, $value = false)
    {
        $values = [
            'is_public' => [
                self::YES_PUBLIC => "公开",
                self::NOT_PUBLIC => "不公开",
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE =>  Yii::t('backend',  'Not Deleted'),                
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    /**
    * [拼接教案名称]
    *
    *    
    **/
    public static function concatMaterial($course_material_id) {
        $name = array();
        if($course_material_id){
            foreach (explode(',', $course_material_id) as $key => $value) {

                 $name[]= CourseMaterial::findOne($value)['name'];
            }
        }
        return $name ? implode(',',$name) : "添加课件";
    }

    public function getAuthors()
    {
        return $this->hasOne(Admin::className(), ['id' => 'author'])->alias('authors');
    }
    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }
}
