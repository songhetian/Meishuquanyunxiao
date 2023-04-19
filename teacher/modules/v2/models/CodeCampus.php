<?php

namespace teacher\modules\v2\models;

#use teacher\modules\v2\models\Classes;
use common\models\Format;

class CodeCampus extends \common\models\Campus
{
	public static $list;

    public function fields()
	{
	    $fields = parent::fields();

	    if($this->classes){
		    $fields[$this->name.'&&&'.$this->id] = function() {
		    	$campus = [];
		    	foreach ($this->classes as $key => $value) {
		    		$campus[] = $value->name.'&&&'.$value->id;
		    	}
		    	return $campus;
		    };
		}else{
			 $fields[$this->name.'&&&'.$this->id] = function() {

			 	 return ["无班级"];
			 };
		};
	    unset(
	    	$fields['name'],
	    	$fields['id'],
	    	$fields['is_main'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status'],
            $fields['studio_id']
        );
	    return $fields;
	}

	public static function getName($string) {

		$name = [];

		$campus = Format::explodeValue($string);

		$list = self::find()
				  ->select('name')
				  ->where(['id'=>$campus])
				  ->asArray()
				  ->all();

		return Format::implodeValue(array_column($list, 'name'));

	}

	public function getClasses()
	{
		 return $this->hasMany(Classes::className(), ['campus_id' => 'id'])->where(['status'=>10])->andFilterWhere(['id' => self::$list])->select('id,name')->alias('classes');
	}

	public static function getAllAdmin($studio_id) {

		$ids =  array_column(self::find()->select('id')->where(['studio_id'=>$studio_id])->asArray()->all(),'id');
		
		return array_column(\teacher\modules\v2\models\Admin::find()->select('id')->andFilterWhere(['or like','campus_id',Format::concatString($ids)])->andWhere(['status'=>self::STATUS_ACTIVE])->asArray()->all(),'id');
		
		

	}
}
