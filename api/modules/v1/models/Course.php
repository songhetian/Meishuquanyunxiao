<?php

namespace api\modules\v1\models;

use Yii;
use backend\models\Admin;
use common\models\Campus;
use common\models\Group;
use common\models\Picture;
use common\models\Video;
use components\Oss;

class Course extends \common\models\Course
{
    public function fields()
	{
	    $fields = parent::fields();
	    
	    if($this->classPeriods){
            $fields['class_period_id'] = function () {
                return [
                    'id' => $this->classPeriods->id,
                    'name' => $this->classPeriods->name,
                    'started_at' => $this->classPeriods->started_at,
                    'dismissed_at' => $this->classPeriods->dismissed_at,
                    'position' => $this->classPeriods->position,
                ];
            };
        }

        if($this->classes){
            $fields['class_id'] = function () {
                return [
                    'id' => $this->classes->id,
                    'name' => $this->classes->name
                ];
            };
        }

        if($this->categorys){
            $fields['category_id'] = function () {
                return [
                    'id' => $this->categorys->id,
                    'name' => $this->categorys->name,
                    'color' => $this->categorys->color
                ];
            };
        }

        if($this->instructors){
            $fields['instructor'] = function () {
                return [
                    'id' => $this->instructors->id,
                    'name' => $this->instructors->name
                ];
            };
        }

        if($this->instructionMethods){
            $fields['instruction_method_id'] = function () {
                return [
                    'id' => $this->instructionMethods->id,
                    'name' => $this->instructionMethods->name
                ];
            };
        }

        if($this->courseMaterials){
            $fields['course_material_id'] = function () {
                $host_info = Yii::$app->request->hostInfo.'/assets';
                
                $description = preg_replace('/api.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->courseMaterials->description));
                $description = preg_replace('/(http|https):\/\//', 'http://', $description);
                return [
                    'id' => $this->courseMaterials->id,
                    'name' => $this->courseMaterials->name,
                    'description' => $description,
                    'picture' => $this->getMaterials(Picture::className(), 'picture', 'image', Group::TYPE_PICTURE),
                    'video' => $this->getMaterials(Video::className(), 'video', 'preview', Group::TYPE_VIDEO),
                ];
            };
        }
        
        $fields['started_at'] = function () {
            return date("Y/m/d", $this->started_at);
        };

        $fields['ended_at'] = function () {
            return date("Y/m/d", $this->ended_at);
        };

	    unset(
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}

    public function getMaterials($table, $dir, $field, $type){
        $groups = Group::findAll([
            'course_material_id' => $this->courseMaterials->id, 
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
                        'id' => $v->id,
                        'image' => $image.Yii::$app->params['oss']['Size']['250x250'],
                        'image_2x' => $image.Yii::$app->params['oss']['Size']['500x500'],
                    ];
                }else{
                    $res[] = [
                        'id' => $v->id,
                        'preview' => $image.Yii::$app->params['oss']['Size']['375x250'],
                        'preview_2x' => $image.Yii::$app->params['oss']['Size']['750x500'],
                    ];
                }
            }
        }
        return ($res) ? $res : [];
    }
}