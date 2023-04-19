<?php

namespace teacher\modules\v2\models;

class ChatGroup extends \common\models\ChatGroup
{

    public function fields()
	{
	    $fields = parent::fields();

	    unset(
	    	$fields['id'],
	    	$fields['studio_id'],
	    	$fields['created_at'],
	    	$fields['updated_at'],
	    	$fields['status']

	    );
	    return $fields;
	}
}