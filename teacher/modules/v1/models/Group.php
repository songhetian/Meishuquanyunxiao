<?php

namespace teacher\modules\v1\models;

use Yii;
#use backend\models\Admin;
use common\models\Video;
use common\models\Campus;
use common\models\Format;
use components\Oss;
use teacher\modules\v1\models\CourseMaterial;

class Group extends \common\models\Group
{
    public function fields()
	{
	    $fields = parent::fields();

	    $fields['group_id'] = function() {
	    	return $this->id;
	    };
	    if($this->material_library_id){
		    $fields['preview'] = function () {
		    	$exp = Format::explodeValue($this->material_library_id);
		        return $this->getPreview($exp);
		    };
		    $fields['material_library_id'] = function () {
		    	$exp = Format::explodeValue($this->material_library_id);
		        return implode(',',array_unique($exp));
		    };
		}else{
		    $fields['preview'] = function () {
		        return null;
		    };
		}
	    if($this->material_library_id){
		    $fields['preview_more'] = function () {
		    	$exp = Format::explodeValue($this->material_library_id);
		        return $this->getMorePreview($exp);
		    };
		}else{
		    $fields['preview_more'] = function () {
		        return null;
		    };
		}
        $fields['number'] = function () {
        	$exp = array_unique(Format::explodeValue($this->material_library_id));
            return count($exp);
        };

	    unset(
	    	$fields['id'],
	    	#$fields['course_material_id'],
	    	$fields['created_at'], 
	    	$fields['updated_at'], 
	    	$fields['status']
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

    //删除多个分组
    public static function delMore($group) {
    	$groups = explode(',',$group);
    	foreach ($groups as $key => $group_id) {
    		$model = self::findOne($group_id);
    		$model->updateStatus();
    	}
    }

	public function getPreview($exp){
		if($this->type== self::TYPE_PICTURE){
			$size = Yii::$app->params['oss']['Size']['320x320'];
			$model = Picture::findOne(current($exp));
			$studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
	    	$image = ($model->source == Picture::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $model->image) : $model->image;
		}else{
			$size = Yii::$app->params['oss']['Size']['475x270'];
			$model = Video::findOne(current($exp));
			$studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
            $image = ($model->source == Video::SOURCE_LOCAL) ? Oss::getUrl($studio, 'video', 'preview', $model->preview) : $model->preview;	
		}
		return $image.$size;
	}

	public function getMorePreview($exp){
			$image = array();
			if($this->type== self::TYPE_PICTURE){
				$size = Yii::$app->params['oss']['Size']['320x320'];
				$array = array_slice($exp, 0 ,4);
				foreach ($array as $key => $value) {
					$model = Picture::findOne($value);
					$studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
			    	$image['img'.($key+1)] = ($model->source == Picture::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $model->image).$size : $model->image.$size;
				}
			}else{
				$size = Yii::$app->params['oss']['Size']['475x270'];
				$array = array_slice($exp, 0 ,4);

				foreach ($array as $key => $value) {
					$model = Video::findOne($value);
					$studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
			    	$image['img'.($key+1)] = ($model->source == Video::SOURCE_LOCAL) ? Oss::getUrl($studio, 'video', 'preview', $model->preview).$size : $model->preview.$size;
				}
			}
			
			return $image;
		}
}