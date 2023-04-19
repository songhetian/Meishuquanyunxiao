<?php

namespace teacher\modules\v2\models;

use common\models\Studio;

class NewActivationCode extends \common\models\ActivationCode
{	
    public function fields()
	{
	    $fields = parent::fields();

	    unset(
	    	$fields['code'],
	    	$fields['relation_id'],
	    	$fields['campus_id'],
	    	$fields['class_id'],
	    	$fields['is_active'],
	    	$fields['studio_id'],
	    	$fields['is_first'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}

	//设置过期时间
	public static function getEndTime($time,$int) {

		$int = ($int == 0) ? 1 : $int;
		if($int >= 1){
            $year = floor($int);
            return strtotime("+$year years +1 day" ,$time);

        }elseif($int < 1 && $int >= 0.09){
            $month =  floor($int*12);
            return strtotime("+$month months +1 day",$time);

        }else{
            $days =  round($int*365)+1;
            return strtotime("+$days days",$time);
        }
	}

	//修改过期时间
	public static function UpdateEndTime($time,$int) {
		#$time = strtotime($time);
		if($int >= 1){
            $year = floor($int);
            return date('Y-m-d',strtotime("+$year years +1 day" ,$time));
        }elseif($year < 1 && $year >= 0.09){
            $month =  floor($int*12);
            return date('Y-m-d',strtotime("+$month months +1 day",$time));
        }else{
            $days =  round($int*365)+1;
            return date('Y-m-d',strtotime("+$days days",$time));
        }
	}

	//获取激活码剩余数量
	public static function getCodeNum($type,$activetime,$studio_id,$num=1) {

		$studio = Studio::findOne($studio_id);

		if($type == 1) {
			$activetime = ($activetime != 0.09) ? array(0,1,2,3) : $activetime;
		}

		if($activetime == 0.09){
			$total_num = self::find()
						  ->select("activation_times")
						  ->where(['studio_id'=>$studio_id,'status'=>10,'activetime'=>$activetime])
						  ->asArray()
						  ->all();

		}else{
			$total_num = self::find()
						  ->select("activation_times")
						  ->where(['type' => $type,'studio_id'=>$studio_id,'status'=>10,'activetime'=>$activetime])
						  ->asArray()
						  ->all();
		}

		$total_num =  array_sum(array_column($total_num,'activation_times'));

		if($type == 1) {
			if($activetime == 0.09){
				$Surplus    =  $studio->one_month_num - $total_num;
			}else{
				$Surplus    =  $studio->teacher_num - $total_num;
			}
		}elseif ($type == 2) {
			if($activetime == 1) {
				$Surplus    =  $studio->one_year_num - $total_num;
			}elseif ($activetime == 2) {
				$Surplus    =  $studio->two_years_num - $total_num;
			}elseif ($activetime == 3) {
				$Surplus    =  $studio->three_years_num - $total_num;
			}elseif ($activetime == 0.25) {
				$Surplus    =  $studio->three_month_num - $total_num;
			}elseif ($activetime == 0.09) {
				$Surplus    =  $studio->one_month_num - $total_num;
			}
			
		}
		return ($Surplus >= $num) ? true :false;
	}
}
