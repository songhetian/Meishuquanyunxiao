<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
#use backend\models\Admin;
use common\models\Format;
use common\models\Category;
use backend\models\ActiveRecord;
use common\models\Campus;

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

    public $activetime;
    public static $press = false; //是否为出版社
    public static $buy_id;
    public static $user_role;
    public $is_buy = false;
    const IS_NEW = 1;
    const IS_HOT = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gather}}';
    }

    public function fields() {

        $fields = parent::fields();

        $fields['cloud_id'] = function() {

            return $this->id;
        };

        $fields['course_material_num'] = function() {

            return self::getNumber($this->course_material_id);
        };

        $fields['category_id'] = function () {

            return ['category_id'=>$this->categorys->id,'name'=>$this->categorys->name];
        };

        $fields['image'] = function () {
            $size = Yii::$app->params['oss']['Size']['950x540'];
            $studio = Campus::findOne(Admin::findOne($this->admin_id)->campus_id)->studio_id;
            return Oss::getUrl($studio, 'picture', 'image', $this->image).$size;
        };

        $fields['course_material_id'] = function() {
            return self::GetMaterialList($this->course_material_id);
        };

        $fields['admin_id'] = function () {

            return [
                'admin_id' => $this->admins->id,
                'name'      => $this->admins->name
            ];
        };

        $fields['author'] = function() {
            
            return  $this->authors->name;
        };
        // $fields['price'] = function() {
        //     if(self::$user_role == 'student'){
        //         if(BuyRecord::GetBuyStatus($this->id,self::$buy_id)) {
        //             return BuyRecord:: GetBuyTime($this->id,self::$buy_id);
        //         }else{
        //             return $this->price;
        //         }
        //     }else{
        //         return $this->price;
        //     }
        // };
        $fields['is_buy'] = function () {
            if(!$this->is_buy){
                if(self::$user_role == 'teacher'){
                    if($this->price != 0.00){
                        return BuyRecord::GetBuyStatus($this->id,self::$buy_id,10);
                    }else{
                        return true;
                    }
                }elseif(self::$user_role == 'student'){
                    if($this->price != 0.00){
                        return BuyRecord::GetBuyStatus($this->id,self::$buy_id,20);
                    }else{
                        return true;
                    }
                }else{
                    return false;
                }
            }else{
                return $this->is_buy;
            }
        };
        $fields['code'] = function () {
            return  'com.meishuquanyunxiao.artworld.courseware'.(int)$this->price;
        };

        unset(
                $fields['id'],
                $fields['course_material_info'],
                $fields['created_at'],
                $fields['updated_at'],
                $fields['studio_id'],
                $fields['status']

            );
        return $fields;
    }

    public function beforeSave($insert)
    {
        $this->course_material_info = self::concatMaterial($this->course_material_id);
        //公共处理
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = time();
                $this->updated_at = time();
            }else{
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
            [['name', 'category_id','image'], 'required','on'=>['create']],
            [['category_id', 'activetime', 'admin_id', 'created_at', 'updated_at','watch_number','is_public'], 'integer'],
            [['price'], 'number'],
            [['introduction'], 'string'],
            [['status','course_material_id'],'safe'],
            [['name'], 'string', 'max' => 100],
            [['course_material_info'], 'string', 'max' => 2000],
            ['is_public', 'default', 'value' => 10],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
                'maxFiles' => 100

            ],
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
            'course_material_id' => '关联教案',
            'activetime' => '有效时长',
            'price'      => '价格',
            'author'     => '教案作者',
            'admin_id'   => '制作人',
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

    //返回课件包数量

    public static function getNumber($string) {

        return count(Format::explodeValue($string));
    }

    //获取课件老师
    public static function getTeacher() {

        return array_unique(array_column(self::find()->select('author')->asArray()->all(), 'author'));
    }

    /*
     *课件名称
     *
    */
    public static function GetMaterialList($course_material_id) {

        $ids = Format::explodeValue($course_material_id);

        return CourseMaterial::find()->where(['id'=>$ids])->all();

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
        return $name ? implode(',',$name) : "添加教案";
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
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
