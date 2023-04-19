<?php 
	namespace teacher\modules\v1\models;

	class UserClass extends \common\models\UserClass
	{
		public function fields()
		{
			$fields = parent::fields();

			return $fields;
		}

		public static function getClassList($class_id) {
				$list = self::find()->select(['user_id'])
								 ->where(['class_id'=>$class_id,'status'=>UserClass::STATUS_ACTIVE])
								 ->asArray()
								 ->all();

				return array_column($list, 'user_id');
		}
	}

 ?>