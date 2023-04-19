<?php 
	
	namespace components;

	use yii;

	class Code {

		public $number;
		public $chars = array(  
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

		public function __construct($number = 1) {

			$this->number = $number;
		}

		public function Create() {
			$array = array();
			for ($i=0; $i < $this->number; $i++) { 
				
				$array[] = $this->rule();
			}
			return $array;
		}
		public function CreateOne() {
			return $this->rule();
		}

		//验证码生产规则
		public function rule() {

			$string = uniqid().$this->Handle(implode('', $this->chars)).mt_rand(0,9999);
			return strtoupper($this->Handle(md5($string)));
		}
		//生成唯一token_value值
		public function CreateToken($id) {

			$string =uniqid().$this->Handle(implode('', $this->chars)).mt_rand(0,9999);
			return md5($id.$this->Handle(md5($string)));
		}

		//生成群口令
		public function CreateGroup(){
			$string =uniqid().$this->Handle(implode('', $this->chars)).mt_rand(0,9999);

			$start = mt_rand(0,25);

			return substr(md5($id.$this->Handle(md5($string))), $start,6);
		}
		//生成图片名
		public function uuid($ext) {
	        $chars = md5(uniqid(mt_rand(), true));  
	        $uuid  = substr($chars, 0, 8) . '-';  
	        $uuid .= substr($chars, 8, 4) . '-';  
	        $uuid .= substr($chars, 12, 4) . '-';  
	        $uuid .= substr($chars, 16, 4) . '-';  
	        $uuid .= substr($chars, 20, 12);
	        $start = mt_rand(0,23);
	        return substr(md5($uuid), $start,6).'.'.$ext;;
		}

		//截取字符串
		public function Handle ($string) {

			$start = mt_rand(0,23);

			return substr($string, $start,8);
		}

	}

 ?>