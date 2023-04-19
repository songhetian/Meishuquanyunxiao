<?php

namespace teacher\modules\v2\models;

class Collection extends \common\models\Collection
{	
    public function fields()
	{
	    $fields = parent::fields();
	    unset(
	    	$fields['id']
        );
	    return $fields;
	}
}
