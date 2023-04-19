<?php
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use teacher\modules\v2\models\Classes;
use common\models\Format;

class Activity extends \common\models\Activity
{
	

    public function fields()
	{
	    $fields = parent::fields();

	    $fields['image']  = function() {
	    	$size = Yii::$app->params['oss']['Size']['fix_width'];
	    	return Oss::getUrl($this->studio_id, 'picture', 'image', $this->image).$size;
	    };

	    unset(
	    	$fields['id'],
	    	$fields['is_top'],
	    	$fields['studio_id'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}
}
