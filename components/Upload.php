<?php 
	namespace components;

	use Yii;
	use components\Oss;
	use components\Code;

	class Upload extends Oss
	{
	    public static function pic_upload($instances, $studio, $table, $field)
	    {
	        $file_name = array();
	        if ($instances) {
	            //$oss = new OSS(true); // 上传文件使用内网，免流量费
		        $oss = new OSS();
		        foreach ($instances as $key => $value) {
		        	$instance = self::array_to_object($value);
		        	
		            $file_name[$key] = $oss->uuid($instance->extension);

		            $ossKey = $studio.'/'.$table.'/'.$field.'/'.$file_name[$key];
		            $filePath = $instance->tempName;
		            $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
		            $oss->ossClient->uploadFile($ossKey, $filePath);
		        }
	            return $file_name;
	        }
	        return $file_name; 
	    }

	    public static function pic_upload1($instances, $studio)
	    {
	        $file_name = array();
	        if ($instances) {
	            //$oss = new OSS(true); // 上传文件使用内网，免流量费
		        $oss = new OSS();
		        $code = new Code;
		        foreach ($instances as $key => $value) {
		        	$instance = self::array_to_object($value);
		        	
		            $file_name[$key] = $code->uuid($instance->extension);

		            $ossKey = $studio.'/'.$file_name[$key];
		            $filePath = $instance->tempName;
		            $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
		            $oss->ossClient->uploadFile($ossKey, $filePath);
		        }
	            return $file_name;
	        }
	        return $file_name; 
	    }

	    public static function UploadQrcode($ossKey, $filePath)
	    {
	        $oss = new OSS();
	        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
	        $oss->ossClient->uploadFile($ossKey, $filePath);
	    }
	    //转为对象
	    public static function array_to_object($arr)
	    {
		    if (gettype($arr) != 'array') {
		        return;
		    }
		    foreach ($arr as $k => $v) {
		        	if($k == 'tmp_name') {
		        		$arr['tempName'] = $v;
		        	}elseif($k == 'name'){
		        		$arr['extension'] = self::getextension($v);
		        	}else{
		        		$arr[$k] = $v;
		        	}
		    }
		 
		    return (object)$arr;
		}

		//获取扩展名

		public static function getextension($str)
		{
			$exps = explode('.', $str);
			return end($exps);
		}
	}
 ?>