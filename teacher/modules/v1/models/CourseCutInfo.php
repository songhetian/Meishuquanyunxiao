<?php 

	namespace teacher\modules\v1\models;

	class CourseCutInfo extends \common\models\CourseCutInfo 
	{

		public static function getAll($course_ids) {

			return self::find()->where(['course_id'=>$course_ids,'status'=>self::STATUS_ACTIVE])->all();
		}
	}
 ?>