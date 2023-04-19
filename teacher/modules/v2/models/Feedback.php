<?php

namespace teacher\modules\v2\models;

class Feedback extends \common\models\Feedback
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
