<?php

namespace teacher\modules\v2\models;
use Yii;


class CountryClassify extends \common\models\CountryClassify
{	
    public function fields()
	{
		$fields = parent::fields();


	    $fields['schools'] = function() {
	    	return $this->schools;
	    };

	    unset(
            $fields['status'],
            $fields['created_at'],
            $fields['updated_at']
        );
	    return $fields;
	}

    public function getSchools()
    {
        return $this->hasMany(School::className(), ['pid' => 'id'])->alias('schools');
    }
}
