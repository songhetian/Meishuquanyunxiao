<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
#use backend\models\Admin;
use backend\models\ActiveRecord;

use common\models\Format;
use components\Oss;
use common\models\Curl;
use teacher\modules\v1\models\Group;
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

    public static $user_role;

    public static $user_id;

    public static $group_id;

    public static $is_my = 1;

    public static function tableName()
    {
        return '{{%picture}}';
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['pic_id'] = function() {
            return $this->id;
        };
        $fields['image'] = function () {
            $size = Yii::$app->params['oss']['Size']['320x320'];

            if(self::$is_my){
                if(self::$user_role == 10){
                    $studio = Admin::findOne(self::$user_id)->studio_id;
                }elseif(self::$user_role == 20){
                    $studio = User::findOne(self::$user_id)->studio_id;
                }
            }else{
                $admin_id = CourseMaterial::findOne(Group::findOne(self::$group_id)->course_material_id)->admin_id;
                $studio = ActivationCode::findOne(['relation_id'=>$admin_id,'type'=>1])->studio_id;
            }
            $image = ($this->source == self::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $this->image) : $this->image;
            
            return $image;
        };

        $fields['size'] = function () {
            $array = array();
            
            $Size = Yii::$app->params['oss']['Size'];
            foreach ($Size as $k => $v) {
                    $array['image_'.$k] = $v;
            }
            return  $array;
        };

        if($this->metis_material_id) {

            $fields['video_url'] = function(){
                 $material = Curl::metis_file_get_contents(
                    Yii::$app->params['metis']['Url']['commodity'].'?id='.$this->metis_material_id
                );
                    foreach ($material as $key => $value) {
                    if($value->video_url){
                        $int  = substr($value->video_url,strripos($value->video_url,'=')+1);
                        $info  = Curl::metis_file_get_contents(
                            Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$int
                        );
                        foreach ($info as $info_key => $info_value) {
                            $array = [];
                            $array['video_id'] = $info_value->id;
                            $array['title']    = $info_value->title;
                            $array['charging_option'] = $info_value->charging_option;
                            $array['cc_id'] = $info_value->chapter;
                            $array['preview_image'] = $info_value->preview_image;
                        }
                        $material[$key]->video_url = $array;
                    }
                }
                 return $array;
            };
        }

        unset(
            $fields['id'],
            $fields['source'],
            $fields['category_id'],
            $fields['keyword_id'],
            $fields['admin_id'],
            $fields['is_public'],
            $fields['created_at'], 
            $fields['updated_at'], 
            $fields['status'],
            $fields['metis_material_id'],
            $fields['publishing_company'],
            $fields['image_group_id'],
            $fields['watch_count']
        );
        return $fields;
    }

    public function beforeSave($insert)
    {
        //公共处理
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                
            }else{
                if ($this->image && $this->image != $this->getOldAttribute('image')) {
                 
                }else{
                    $this->image = $this->getOldAttribute('image');
                }
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
            //[['category_id'], 'required'],
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
                'maxSize' => 1024 * 5000,
                'minWidth' => 150,
                'minHeight' => 150,
                'maxFiles' => 100
            ],
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
    //获取头像地址
    public static function getImage($user_id,$image,$user_type){
        
        switch ($user_type) {
            case 1:
                $studio = Campus::findOne((Admin::findOne($user_id)->campus_id))->studio_id;
                break;
            case 2:
                $user = User::findOne($user_id);
                if($user->is_image == 1){
                    $studio = 'student';
                }else{
                    $studio = $user->studio_id;
                }
                break;
            case 3:
                $studio = 'family';
                break;
        }
        $size = Yii::$app->params['oss']['Size']['320x320'];
        return Oss::getUrl($studio, 'picture', 'image', $image).$size;
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
?>
