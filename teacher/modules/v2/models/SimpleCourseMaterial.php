<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use backend\models\ActiveRecord;
use teacher\modules\v1\models\Picture;
use teacher\modules\v1\models\Video;
use teacher\modules\v1\models\Group;
use teacher\modules\v1\models\Course;

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
class SimpleCourseMaterial extends ActiveRecord
{
    const PUBLIC_NOT_YET = 0;
    const PUBLIC_ED = 10;

    public static function tableName()
    {
        return '{{%course_material}}';
    }
    public function fields()
    {
        $fields = parent::fields();

        $fields['instructor'] = function () {
            return [
                'instructor_id' => $this->instructors->id,
                'name' => $this->instructors->name
            ];
        };
	    $fields['course_material_id'] = function () {
	        $host_info = Yii::$app->request->hostInfo.'/assets';
	        $description = preg_replace('/api.teacher.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->description));
	        $description = preg_replace('/(http|https):\/\//', 'http://', $description);
	        return [
	            'course_material_id' => $this->id,
	            'admin_id'           => $this->admin_id,
	            'preview_image'      => $this->getPreview($this->id),
	            'name' => $this->name,
	            'depict' => $description,
	            'picture' => $this->getMaterials(Picture::className(), 'picture', 'image', Group::TYPE_PICTURE),
	            'video' =>  $this->getMaterials(Video::className(), 'video', 'preview', Group::TYPE_VIDEO),
	        ];
	    };
	    unset($fields['id'],
	    	  $fields['admin_id'],
	    	  $fields['name'],
	    	  $fields['description']
	    	);
	    return $fields;
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

    //获取课件最大上课时间
    public static function getMaxTime($course_material_id) {

        return Course::find()->where(['course_material_id'=>$course_material_id,'status'=>10])->max('ended_at');
    }
    
    public function getInstructors()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('instructors');
    }
}
