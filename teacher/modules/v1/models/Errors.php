<?php 
	namespace teacher\modules\v1\models;

	class Errors {


		public static function getInfo($error) {

			$errors = "";

			foreach ($error as $key => $value) {
				foreach ($value as $k => $v) {
					$errors.=$v;
				}
			}
			return $errors;
		}
	}
 ?>