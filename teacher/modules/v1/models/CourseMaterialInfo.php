<?php 
	namespace teacher\modules\v1\models;

	use Yii;
	use common\models\Format;

	class CourseMaterialInfo extends \common\models\CourseMaterialInfo
	{


		public static function getInfo($course_material_id) {

			$model = self::find()->where(['course_material_id'=>$course_material_id])->one();

			return ($model !== null) ? $model : false ;
		}
		//教案列表
		public static function GetIds($admin_id) {

			$model = self::find()->select('course_material_id')
								 ->andFilterwhere(['admin_id'=>$admin_id])
								 ->indexBy('course_material_id')
								 ->all();

			return array_keys($model);
		}

		public static function getAll($admin_ids,$category_id) {
			$model = self::find()->select('course_material_id')
								 ->where(['admin_id'=>$admin_ids])
								 ->andFilterwhere(['category_id'=>Format::explodeValue($category_id)])
								 ->indexBy('course_material_id')
								 ->all();

			return array_keys($model);
		}

		//教案列表
		public static function GetSearchIds($VisuaCategory,$course_material_ids,$search_admin_id='',$category_id='',$time='') {

			$search_admin_id = ($search_admin_id == 0)? NULL : $search_admin_id;

			$category_id     = ($category_id == 0) ? Format::explodeValue($VisuaCategory) : $category_id;
		
			$time    		 = ($time == 0 )? NULL : $time;
			$model = self::find()->select('course_material_id')
								 ->where(['course_material_id'=>$course_material_ids])
								 ->andFilterwhere(['admin_id'=>$search_admin_id])
								 ->andFilterwhere(['category_id' => $category_id])
								 ->andFilterwhere(['>=','created_at',$time])
								 ->indexBy('course_material_id')
								 ->all();

			return array_keys($model);
		}

	}