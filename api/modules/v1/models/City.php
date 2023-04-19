<?php

namespace api\modules\v1\models;

class City extends \common\models\City
{
    public function fields()
	{
	    $fields = parent::fields();
	    unset(
	    	$fields['pid'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}
}
