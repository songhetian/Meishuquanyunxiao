<?php

namespace teacher\modules\v2\models;

use teacher\modules\v2\models\Classes;
use common\models\Format;

class NewCampus extends \common\models\Campus
{
	public $classes;

    public function fields()
	{
	    $fields = parent::fields();

	    $fields['campus_id'] = function() {
	    	
	    	return $this->id;
	    };

	   $fields['lat'] = function ()  {
	   		if($this->lat) {
	   			return $this->lat;
	   		}else{
	   			return "";
	   		}
	   };

	   $fields['lng'] = function ()  {
	   		if($this->lng) {
	   			return $this->lng;
	   		}else{
	   			return "";
	   		}
	   };

	    unset(
	    	$fields['id'],
	    	$fields['is_main'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status'],
            $fields['studio_id']
        );
	    return $fields;
	}
}
