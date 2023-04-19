<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\models\Campus;
use common\models\Studio;

class Format extends Model
{
	static public function explodeValue($value)
	{
		if(is_array($value)){
			return $value;
		}
		return ($value) ? explode(',', $value) : [];
	}

	static public function implodeValue($value)
	{
		return rtrim(implode(',', $value), ',');
	}

	static public function concatField($field)
	{
		return "CONCAT(','," . $field . ",',')";
	}

	static public function concatString($value)
	{
		$exps = self::explodeValue($value);
		$res = [];
		foreach ($exps as $v) {
			$res[] = ',' . $v . ',';
		}
        return $res;
    }

	static public function mb_substr($value, $reveal_all = false, $start = 0, $len = 50)
	{
		return (strlen($value) < $len || $reveal_all == true) ? $value : mb_substr($value, $start, $len, 'UTF-8') . '...';
	}
	
	static public function getModelName($className)
	{
		$arr = explode('\\', $className);
		return end($arr);
	}

	static public function getStudio($field, $campus_id = 0)
	{
		$campus_id = ($campus_id) ? $campus_id : Yii::$app->user->identity->campus_id;
		$exps = self::explodeValue($campus_id);
		$studio_id = Campus::findOne(current($exps))->studio_id;
		return Studio::findOne($studio_id)->$field;
	}

	/**
	 *[删除字符串中的某些字符]
	 *@param local_string 字符串  del_string 删除字符串
	 *
	 */
	static public function deleteFilterString($local_string,$del_string)
	{
		$del_string_array = explode(',', $del_string);

		$local_string_array  = explode(',', $local_string);

		foreach ($local_string_array as $key => $value) {
			if(in_array($value, $del_string_array))
			{
				unset($local_string_array[$key]);
			}
		}
		return implode(',', $local_string_array);
	}

	/**
	 *[添加字符串中的某些字符]
	 *@param local_string 字符串  string 添加字符串
	 *
	 */
	static public function addFilterString($local_string,$string)
	{
		if(empty($local_string)) {

			return $string;
		}else{
			return $local_string.','.$string;
		}
	}

	//删除数组中特定值
	public static function delArrayValue($array,$taget)
	{

		foreach ($array as $key => $value) {
			if($value == $target) {
				unset($array[$key]);
			}
		}
		return $array;
	}

	//字符串反转
	public static function Reverse($string)
	{
		$array = explode(',', $string);
		
		return implode(',',array_reverse($array));
	}

	//增加有效时间
	public static function addYears($time,$int)
	{
		return  strtotime("+$int year",$time);
	}


	//获取到期时间(新)
    public static function EndTime($time) {

    	return ceil((strtotime($time) - time())/24/3600);
    }	
    //获取过期时间
    public static function getExpire($time,$year) {
        if($year >= 1){
            $year = floor($year);
            return strtotime("+$year years" ,$time)-time();
        }elseif($year < 1 && $year >= 0.09){
            $month =  floor($year*12);
            return strtotime("+$month months",$time)-time();
        }else{
            $days =  round($year*365);
            return strtotime("+$days days",$time)-time();
        }
    }

    //短信验证
    public static function ReturnDuanXin($time,$code) {

    	$int =  time() - ceil($time/1000);

    	if($int > 60) {

    		return false;
    	}

    	$Guize  = Yii::$app->params['DuanXin'];

    	$arr1  = str_split($time);

    	$count = count($arr1);

    	$array2 = array();

    	foreach ($arr1 as $key => $value) {
    		if($key < 6) {
    			$array2[] = $Guize[($value*10+$arr1[$count-$key-1])%count($Guize)];
    		}
    	}
    	return  (implode('',$array2) == $code) ? true : false;
    }

	public static function isMobile(){  
	    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';  
	    $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';        
	    function CheckSubstrs($substrs,$text){  
	        foreach($substrs as $substr)  
	            if(false!==strpos($text,$substr)){  
	                return true;  
	            }  
	            return false;  
	    }
	    $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
	    $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod','HarmonyOS');  
	          
	    $found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||  
	              CheckSubstrs($mobile_token_list,$useragent);  
	          
	    if ($found_mobile){  
	        return true;  
	    }else{  
	        return false;  
	    }  
	}



}