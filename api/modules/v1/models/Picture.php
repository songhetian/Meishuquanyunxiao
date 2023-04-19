<?php

namespace api\modules\v1\models;

use Yii;
use backend\models\Admin;
use common\models\Campus;
use components\Oss;
use common\models\Curl;

class Picture extends \common\models\Picture
{

    public function fields()
	{
	    $fields = parent::fields();
	    
	    $fields['image'] = function () {
	    	$size = Yii::$app->params['oss']['Size']['500x500'];
	    	$studio = Campus::findOne(Admin::findOne($this->admin_id)->campus_id)->studio_id;
	    	$image = ($this->source == self::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $this->image) : $this->image;
            return $image.$size;
        };
        $fields['image_original'] = function () {
        	$studio = Campus::findOne(Admin::findOne($this->admin_id)->campus_id)->studio_id;
	    	$image = ($this->source == self::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $this->image) : $this->image;
            return $image;
        };

        $fields['publishing_company'] = function () {
            return ($this->publishing_company) ? $this->publishing_company : Yii::t('api', 'Local Studio');
        };
        $fields['watch_count'] = function () {
        	return $this->watch_count + 1;
        };
	    unset(
	    	$fields['source'],
	    	$fields['name'],
	    	$fields['category_id'],
	    	$fields['keyword_id'],
	    	$fields['admin_id'],
	    	$fields['is_public'],
	    	$fields['created_at'], 
	    	$fields['updated_at'], 
	    	$fields['status']
	    );
	    return $fields;
	}

	public static function serach($search_input,$limit,$page) {
        $material = Curl::metis_file_get_contents(
           Yii::$app->params['metis']['Url']['picture_search'].'?search_input='.$search_input.'&limit='.$limit.'&page='.$page
        );

        return $material;
	}
}
