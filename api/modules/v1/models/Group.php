<?php

namespace api\modules\v1\models;

use Yii;
use backend\models\Admin;
use common\models\Picture;
use common\models\Video;
use common\models\Campus;
use common\models\Format;
use components\Oss;

class Group extends \common\models\Group
{
    public function fields()
	{
	    $fields = parent::fields();
	    
	    $fields['preview'] = function () {
	    	$exp = Format::explodeValue($this->material_library_id);
	        return $this->getPreview($exp);
	    };

        $fields['number'] = function () {
        	$exp = Format::explodeValue($this->material_library_id);
            return count($exp);
        };

	    unset(
	    	$fields['course_material_id'],
	    	$fields['type'], 
	    	$fields['created_at'], 
	    	$fields['updated_at'], 
	    	$fields['status']
	    );
	    return $fields;
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
}
