<?php

namespace api\modules\v1\models;

class Campus extends \common\models\Campus
{

    public function fields()
	{
	    $fields = parent::fields();
	    unset(
	    	$fields['is_main'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}
}
