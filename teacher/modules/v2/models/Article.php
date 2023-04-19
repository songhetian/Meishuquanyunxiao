<?php

namespace teacher\modules\v2\models;
use Yii;


class Article extends \common\models\Article
{	
    public function fields()
	{
		$fields = parent::fields();
		$fields['time'] = function() {

			return date('Y-m-d',$this->time);
		};
	    unset(
	    	$fields['classify_id'],
	    	$fields['created_at'],
	    	$fields['updated_at'],
	    	$fields['pid'],
            $fields['status']
        );
	    return $fields;
	}
}
