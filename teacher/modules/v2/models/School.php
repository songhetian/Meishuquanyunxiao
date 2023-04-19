<?php

namespace teacher\modules\v2\models;
use Yii;


class School extends \common\models\School
{	
	public static $is_show = 0;
	public $module;

    public function fields()
	{
		$fields = parent::fields();
	    unset(
            $fields['status'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['pid'],
            $fields['image']
        );
	    $fields['pic'] = function () {
	    	return $this->image;
	    };
	    if(self::$is_show) {
	    	unset($fields['depiction']);
	    	$fields['des'] = function() {
	    		return $this->depiction;
	    	};
	    	$fields['module'] = function() {
	    		return $this->GetCategory($this->id);
	    	};
	    }else{
	    	unset($fields['depiction']);
	    }

	    return $fields;
	}


	//获取分类
	public function GetCategory($school_id) {

		$category = Yii::$app->params['Category'];

        $list = array();
        
        foreach ($category as $key => $value) {
           $list[] = array('text'=>$value,'id'=>$key,'school_id'=>$school_id);
        }

        return $list;
	}
}
