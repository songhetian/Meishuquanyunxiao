<?php

namespace teacher\modules\v1\models;

use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Classes;

class Campus extends \common\models\Campus
{
	public static $list;

    public function fields()
	{
	    $fields = parent::fields();
	    $fields['campus_id'] = function() {
	    	return $this->id;
	    };

	    $fields['classes'] = function() {
	    	return $this->classes;
	    };
	    unset(
	    	$fields['id'],
	    	$fields['is_main'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status'],
            $fields['studio_id']
        );
	    return $fields;
	}

	public function getClasses()
	{
		 return $this->hasMany(Classes::className(), ['campus_id' => 'id'])->andFilterWhere(['id' => self::$list])->select('id,name')->alias('classes');
	}
}
