<?php

namespace teacher\modules\v2\models;

class Race extends \common\models\Race
{

    public function fields()
	{
	    $fields = parent::fields();
	    unset(
	    	$fields['created_at'], 
	    	$fields['updated_at'], 
	    	$fields['status']
	    );
	    return $fields;
	}
}
