<?php 
	namespace components;

	use Yii;

	class ImageHandle extends Oss
	{

		public static function Create($path1,$path2,$file_path= '',$name = "",$filename) {
			
			$im1 = imagecreatefromjpeg($path1); 
			$im2 = imagecreatefromjpeg($path2);


			$color = imagecolorallocate($im1,255,255,255);

			$font = dirname(Yii::$app->BasePath)."/teacher/web/upload/ziti.ttf";

			$size = 90;

			imagettftext($im1, $size, 0, 290, 3770, $color, $font, $name);

			imagecopymerge($im1, $im2, 2600, 3630, 0, 0, imagesx($im2), imagesy($im2), 100);

			imagejpeg($im1, $file_path.'/'.$filename); 
			imagedestroy($im1); //销毁图像，释放资源 
			imagedestroy($im2); //销毁图像，释放资源 
		}

	}
 ?>