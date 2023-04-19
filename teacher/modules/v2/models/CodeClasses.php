<?php 
	
namespace teacher\modules\v2\models;

use common\models\Format;

class CodeClasses extends \common\models\Classes
{
	public function fields()
	{
	    $fields = parent::fields();

	    $fields['class_id'] = function() {
	    	return $this->id;
	    };
        $fields['title'] = function() {

        	return $this->campuses->name.'---'.$this->name;
        };
	    unset(
	    	$fields['id'],
	    	$fields['name'],
	    	$fields['created_at'],
	    	$fields['updated_at'],
	    	$fields['year'],
	    	$fields['campus_id'],
	    	$fields['supervisor'],
	    	$fields['note'],
	    	$fields['status']
	    );
	    return $fields;
	}
	//获取可见班级
	public static function getClsses($admin_id,$campus_id) {

		$classes =  Format::explodeValue(Admin::findOne($admin_id)['class_id']);
		return self::find()
			  ->where(['campus_id'=>$campus_id,'status'=>self::STATUS_ACTIVE])
			  ->andFilterWhere(['id'=>$classes])
			  ->all();
	}

}

?>