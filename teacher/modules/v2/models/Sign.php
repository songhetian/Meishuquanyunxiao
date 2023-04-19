<?php

namespace teacher\modules\v2\models;

class Sign extends \common\models\Sign
{

    public function fields()
	{
	    $fields = parent::fields();

	    $fields['name'] = function() {
	    	return $this->users->name;
	    };

		$fields['identifier'] = function () {

			return 'student'.$this->users->id;
		};

	    $fields['created_at'] = function() {
	    	return date("Y-m-d H:i:s",$this->created_at);
	    };
	    unset(
	    	$fields['id'],
	    	$fields['class_id'],
	    	$fields['class_period_id'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}

	public static function GetNum($course_id) {
		return self::find()
				  ->where([
				  			'course_id'=>$course_id,
				  			'status'   => 10,
				  		])
				  ->count();
	}



	public static function Check($course_id,$class_period_id,$user_id) {

		$time  = date("Y-m-d",Course::findone($course_id)->started_at);

		$start = ClassPeriod::findOne($class_period_id)->started_at;

		$end   =  ClassPeriod::findOne($class_period_id)->dismissed_at;
		
		if(time() < strtotime("-30 minutes",strtotime($time.' '.$start))) {
			return 1;
		}elseif(time() > strtotime($time.' '.$end)) {
			return 2;
		}

		if(self::findOne(['course_id'=>$course_id,'user_id'=>$user_id,'status'=>10])){
				return 3;
			} 
	}


}
