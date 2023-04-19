<?php 
	
namespace teacher\modules\v1\models;

use teacher\modules\v1\models\Campus;
use teacher\modules\v1\models\User;

class Classes extends \common\models\Classes
{
	public function fields()
	{
	    $fields = parent::fields();
	    $fields['class_id'] = function() {
	    	return $this->id;
	    };
	    unset(
	    	$fields['id']
	    );
	    return $fields;
	}
}

?>