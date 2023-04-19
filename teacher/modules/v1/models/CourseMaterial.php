<?php

namespace teacher\modules\v1\models;

use Yii;
use backend\models\ActiveRecord;
use teacher\modules\v1\models\Picture;
use components\Oss;
use teacher\modules\v1\models\Group;
use teacher\modules\v1\models\Admin;

/**
 * This is the model class for table "{{%course_material}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $admin_id
 * @property integer $is_public
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class CourseMaterial extends ActiveRecord
{
    const PUBLIC_NOT_YET = 0;
    const PUBLIC_ED = 10;

    public static function tableName()
    {
        return '{{%course_material}}';
    }
    public function fields() {

        $fields = parent::fields();

        $fields['material_id'] = function () {

            return $this->id;
        };

        $fields['preview_image'] = function () {

            return $this->getPreview($this->id);
        };

        $fields['pic_number'] = function () {
            return $this->getNum($this->id,Group::TYPE_PICTURE);
        };

        $fields['vid_number'] = function () {
            return $this->getNum($this->id,Group::TYPE_VIDEO);
        };

        $fields['depict'] = function () {
            $host_info = Yii::$app->request->hostInfo.'/assets';
            $description = preg_replace('/api.teacher.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->description));
            $description = preg_replace('/(http|https):\/\//', 'http://', $description);
            return $description;
        };
        $fields['updated_at_days'] = function () {

             return date("Y/m/d", $this->updated_at);;
        };
        $fields['admin_name'] = function() {
            return Admin::findOne($this->admin_id)->name;
        };
        unset(
            $fields['id'],
            $fields['is_public'],
            $fields['status'],
            $fields['created_at'],
            $fields['description']
        );
        return $fields;
    }
    public function beforeSave($insert)
    {
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

    public function rules()
    {
        return [
            //特殊需求
            [['name'], 'required'],
            //字段规范
            ['is_public', 'default', 'value' => self::PUBLIC_ED], 
            ['is_public', 'in', 'range' => [self::PUBLIC_NOT_YET, self::PUBLIC_ED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['description'], 'string'],
            [['admin_id', 'is_public', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['picture', 'video'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '课题名称'),
            'description' => Yii::t('app', '图文'),
            'admin_id' => Yii::t('app', '上传者'),
            'is_public' => Yii::t('app', '是否公开'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public function getPreview($id) {
        $size = Yii::$app->params['oss']['Size']['320x320'];
        $material_library_id = Group::find()->where(['type'=>Group::TYPE_PICTURE,'status'=>Group::STATUS_ACTIVE,'course_material_id'=>$id])->one();

        $pic_id =  current(explode(',',$material_library_id['material_library_id']));
        $image =  Picture::findOne($pic_id);
        if($image) {
            $size = Yii::$app->params['oss']['Size']['320x320'];
            $studio = Campus::findOne(Admin::findOne($image->admin_id)->campus_id)->studio_id;
            $image = ($image->source == Picture::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $image->image) : $image->image;
            return $image.$size;
        }else{
            return null;
        }
    }

    public function getNum($id,$type) {
        $material_library_ids = Group::find()->where(['type'=>$type,'status'=>Group::STATUS_ACTIVE,'course_material_id'=>$id])->all();
        $str = '';
        foreach ($material_library_ids as $key => $value) {
            if(!empty($value['material_library_id'])){
                $str.=$value['material_library_id'].',';
            }
        }
        if(!empty($str)){
            return count(array_filter(array_unique(explode(',', $str))));
        }else{
            return 0;
        }
    }

    public static function createModel() {

        return new self();
    }

    public function getMaterials($table, $dir, $field, $type){
        $groups = Group::findAll([
            'course_material_id' => $this->id, 
            'type' => $type, 
            'status' => self::STATUS_ACTIVE
        ]);

        foreach ($groups as $value) {
            $material_library_id .= $value->material_library_id . ',';
        }
        $ids = explode(',', $material_library_id);
        $model = $table::findAll($ids);
        $res = [];
        if($model){
            foreach ($model as $v) {
                //判断图片来源
                $studio = Campus::findOne(Admin::findOne($v->admin_id)->campus_id)->studio_id;
                $image = ($v->source == $table::SOURCE_LOCAL) ? Oss::getUrl($studio, $dir, $field, $v->$field) : $v->$field;
                if($type == Group::TYPE_PICTURE){
                    $res[] = [
                        'image_id' => $v->id,
                        'image' => $image.Yii::$app->params['oss']['Size']['250x250'],
                        'image_2x' => $image.Yii::$app->params['oss']['Size']['500x500'],
                    ];
                }else{
                    $res[] = [
                        'video_id'        => $v->id,
                        'title'           => $v->name,
                        'charging_option' => ($v->charging_option)/10,
                        'cc_id'           => $v->cc_id,
                        'preview'         => $image.Yii::$app->params['oss']['Size']['375x250'],
                        'preview_image'   => $image.Yii::$app->params['oss']['Size']['375x250'],
                    ];
                }
            }
        }
        return ($res) ? $res : [];
    }

    //获取教案列表
    public static function getList($admin_id,$category_id='') {

       $admin_ids  =  array_column(Admin::getVisua($admin_id),'admin_id');

       $ids        =  CourseMaterialInfo::GetIds($admin_ids,$category_id);
       
       return self::find()->where(['id'=>$ids,'status'=>self::STATUS_ACTIVE])->orderBy('updated_at DESC,created_at DESC');
       

    }

    //教案搜索
    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
