<?php 
	
namespace teacher\modules\v2\models;

use common\models\Format;

class Classes extends \common\models\Classes
{
	public function fields()
	{
	    $fields = parent::fields();
	    $fields['class_id'] = function() {
	    	return $this->id;
	    };

        if($this->supervisor){
            $fields['supervisor'] = function () {
                return $this->supervisors->name;
            };
        }

        $fields['user_count'] = function () {
            return User::find()->where(['class_id' => $this->id,'status'=>10])->count();
        };
	    unset(
	    	$fields['created_at'],
	    	$fields['updated_at']
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
	//获取可见班级
	public static function getClsses($admin_id,$campus_id) {

		$classes =  Format::explodeValue(Admin::findOne($admin_id)['class_id']);

		return self::find()
			  ->select(['class_id'=>'id','name'])
			  ->where(['campus_id'=>$campus_id,'status'=>self::STATUS_ACTIVE])
			  ->andFilterWhere(['id'=>$classes])
			  ->asArray()
			  ->all();
	}
	//获取可见班级
	public static function getClsses1($admin_id,$campus_id) {

		$classes =  Format::explodeValue(Admin::findOne($admin_id)['class_id']);

		return self::find()
			  ->select(['title'=>'name','class_id'=>'id'])
			  ->where(['campus_id'=>$campus_id,'status'=>self::STATUS_ACTIVE])
			  ->andFilterWhere(['id'=>$classes])
			  ->asArray()
			  ->all();
	}
}

?>