<?php

namespace components;

use Yii;
use common\models\Video;

class Spark {
	/**
	 * 生成可供Spark使用的URL参数
	 * @param Array $info 待发送的数组
	 * @param int $time 请求时间
	 * @param String $salt KEY
	 */
	static function get_hashed_query_string($info, $time, $salt) 
	{
		$qs = self::get_query_string($info);
		$hash = self::get_hashed_value($qs, $time, $salt);
		$htqs = $qs . "&time=$time&hash=$hash";
		return $htqs;
	}
	
	/**
	 * 生成THQS算法的hash值
	 *
	 */
	static function get_hashed_value($qs, $time, $salt) 
	{
		return strtoupper(md5($qs . "&time=$time&salt=$salt"));
	}
	
	/**
	 * 生成THQS算法的信息查询串（Query string）
	 *
	 */
	static function get_query_string($info) 
	{
		ksort($info);
		return self::http_build_query($info);
	}

	/**
	 * 根据数组生成HTTP请求URL参数
	 * PHP4 版本
	 * @param unknown_type $array
	 */
	static function http_build_query($array) 
	{
		$query = '';
		foreach ($array as $key => $value) {
			$key = self::urlencode($key);
			$value = self::urlencode($value);
			$query .= "$key=$value&";
		}
		
		if (strlen($query)) {
			$query = substr($query, 0, -1);
		}
		return $query;
	}

	/**
	 * *不要被转义了。
	 * @param $string
	 */
	static function urlencode($string) 
	{
		$string = str_replace('*', '-tSl2nWmMsagD-gEr', $string);
		$string = urlencode($string);
		return str_replace('-tSl2nWmMsagD-gEr', '*', $string);
	}

	//获取视频时长
	static function getDuration($cc_id, $charging_option)
	{
		$type = ($charging_option == Video::CHARGING_NORMAL) ? 'normal' : 'encrypt';
		$info = [
            'userid' => Yii::$app->params['spark'][$type]['userid'],
            'videoid' => $cc_id,
            'format' => 'json'
        ];
       	$video = Yii::$app->params['spark']['url']['video'];
        $result = self::getResult($video, $type, $info);
        return $result['video']['duration'];
	}

	//获取视频播放代码
	static function getPlayCode($cc_id, $charging_option)
	{
		$type = ($charging_option == Video::CHARGING_NORMAL) ? 'normal' : 'encrypt';
		$video = Yii::$app->params['spark']['url']['video'];
        $playcode = Yii::$app->params['spark']['url']['playcode'];
		$info = [
            'userid' => Yii::$app->params['spark'][$type]['userid'],
            'videoid' => $cc_id,
            "player_width" => 500,
			"player_height" => 250,
            'format' => 'json'
        ];
        $result = self::getResult($video.$playcode, $type, $info);
		return $result['video']['playcode'];
	}

	static function getResult($action, $type, $info){
		$time = time();
        $salt = Yii::$app->params['spark'][$type]['key'];
        $main = Yii::$app->params['spark']['url']['main'];
        $request_url = $main.$action.'?'.self::get_hashed_query_string($info, $time, $salt);
		$result = file($request_url);
		if(!empty($result['error'])){
			$error = "";
			switch ($result['error']) {
				case 'INVALID_REQUEST':
					$error = '用户输入参数错误'; 
					break;
				case 'SPACE_NOT_ENOUGH':
					$error = '用户剩余空间不足'; 
					break;
				case 'SERVICE_EXPIRED':
					$error = '用户服务终止'; 
					break;
				case 'PROCESS_FAIL':
					$error = '服务器处理失败'; 
					break;
				case 'TOO_MANY_REQUEST':
					$error = '访问过于频繁'; 
					break;
				case 'PERMISSION_DENY':
					$error = '用户服务无权限'; 
					break;
				
				default:
					$error = '未知错误，请重试'; 
					break;
			}
			echo "{\"error\":\"$error\"}";
			exit;
		}
		return json_decode($result[0],true);
	}
}