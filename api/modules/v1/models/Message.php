<?php

namespace api\modules\v1\models;

class Message extends \common\models\Message
{

    public function fields()
	{
	    $fields = parent::fields();
	    
	    $fields['created_at'] = function () {
            return date("Y.m.d", $this->created_at);
        };

	    unset(
	    	$fields['message_category_id'],
	    	$fields['campus_id'],
	    	$fields['category_id'],
	    	$fields['class_id'],
	    	$fields['user_id'],
	    	$fields['admin_id'],
	    	$fields['updated_at'], 
	    	$fields['status']
	    );
	    return $fields;
	}
}
