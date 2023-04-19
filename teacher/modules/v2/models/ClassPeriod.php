<?php 
	namespace teacher\modules\v2\models;
	use Yii;

	class ClassPeriod extends \common\models\ClassPeriod
	{
	    public function beforeSave($insert)
	    {
	    	return true;
	    }

		public function fields()
		{
			 $fields = parent::fields();
			 $fields['class_period_id'] = function() {
			 	return $this->id;
			 };
			 unset(
			 	$fields['id'],
			 	$fields['position'],
			 	$fields['created_at'],
			 	$fields['updated_at'],
			 	$fields['status']
			 );
			 return $fields;
		}

		//根据班级获取上课时间信息
		public static function getInfo($class_id) {

			$campus_id =  Classes::findOne($class_id)['campus_id'];

			$studio_id = Campus::findOne($campus_id)['studio_id'];

			return self::find()->select(['class_period_id'=>'id','name','started_at','dismissed_at'])->where(['studio_id'=>$studio_id,'status'=>ClassPeriod::STATUS_ACTIVE])->orderBy('position')->asArray()->indexBy('class_period_id')->all();
		}
	}

 ?>