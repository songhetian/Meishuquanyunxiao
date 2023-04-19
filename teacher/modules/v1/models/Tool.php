<?php 
	namespace teacher\modules\v1\models;

	class Tool {

		//计算两个时间的差值
		public static function DiffDays($started_at,$ended_at) {
			// $started_at = strtotime($started_at);
			// $ended_at   = strtotime($ended_at);
			return  ceil(($ended_at-$started_at)/3600/24);
		}
	}
 ?>