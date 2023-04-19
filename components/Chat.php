<?php 
	
	namespace components;

	use yii;

	class Chat {

		public $identifier;
		public $sdkappid = 1400055041;
		public $private_key_path = "/home/keys/private_key";

		public function __construct($sdkappid,$identifier,$private_key_path) {
			$this->identifier = $identifier;
			$this->private_key_path  = $private_key_path;
			$this->sdkappid  = $sdkappid;
		}


		public  function Signature()
		{

		    # 这里需要写绝对路径，开发者根据自己的路径进行调整
		    $command = '/home/tls_sig_api/bin/signature'
		    . ' ' . escapeshellarg($this->private_key_path)
		    . ' ' . escapeshellarg($this->sdkappid)
		    . ' ' . escapeshellarg($this->identifier);
		    $ret = exec($command, $out, $status);
		    if ($status == -1)
		    {
		        return null;
		    }
		    return $out;
		}

	}
 ?>