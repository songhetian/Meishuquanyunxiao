<?php

namespace teacher\modules\v2\models;

class ErrorLog extends \common\models\ErrorLog
{

    public function fields()
	{
	    $fields = parent::fields();

	    $fields['name'] = function () {
	    	if($this->role == 1) {
	    		return Admin::findOne($this->admin_id)->name;
	    	}elseif($this->role == 2) {
	    		return User::findOne($this->admin_id)->name;
	    	}elseif ($this->role == 3) {
	    		return Family::findOne($this->admin_id)->name;
	    	}
	    };

	    $fields['studio_name'] = function () {	
	    	return $this->studios->name;
	    };

	    unset(
	    	$fields['status'],
	    	$fields['created_at'],
	    	$fields['updated_at']
	    );
	    return $fields;
	}
}
