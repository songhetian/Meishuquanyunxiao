<?php  

	use yii\helpers\Html;
?>

<!DOCTYPE html>
<html>

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<title><?= $model->studios->name ?></title>
		<link rel="stylesheet" type="text/css" href="<?= Yii::$app->request->baseUrl; ?>/assets/css/down.css">
		<script type="text/javascript">
			function is_ios() {
				var u = navigator.userAgent;
				return !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
			}

			function is_android() {
				var u = navigator.userAgent;
				return u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
			}

			function open(url) {
				var a = document.createElement("a");
				if(!a.click) {
					window.location = url;
					return;
				}
				a.setAttribute("href", url);
				a.style.display = "none";
				document.body.appendChild(a);
				a.click();
			}
			//判断是否微信登陆
			function isWeiXin() {
				var ua = window.navigator.userAgent.toLowerCase();
				console.log(ua);
				if (ua.match(/MicroMessenger/i) == 'micromessenger') {
					return true;
				} else {
					return false;
				}
			}
			
		</script>
	</head>

	<body>
		<div class="warp">
			<section class="download_boxs">
				<!--机构LOGO-->
				<div class="studio_logos">
					<img src="<?= $model->logo.Yii::$app->params['oss']['Size']['512x512'] ?>">
				</div>
				<!--机构名称-->
				<div class="studio_name">
					<a><?= $model->studios->name ?></a>
				</div>
				<div class="xinren studio_name" style="display: none;">
					<?= Html::a('"未受信任的企业级开发者"的解决办法"','test.html',['style'=>['color'=>'blue','text-decoration'=>'underline']])?>
				</div>
				<!--立即下载-->
				<div class="download_box ">
					<a class="download tc block">立即下载</a>
				</div>
				<!--支持设备图标-->
				<div class="icons">
					<a class="apple_icon" href="itms-services://?action=download-manifest&amp;url=<?= $model->plist ?>">
						<img src="<?= Yii::$app->request->baseUrl; ?>/assets/images/down/apple_icon.png" alt="苹果"></a>
					<span></span>
					<a class="android_icon download">
						<img src="<?= Yii::$app->request->baseUrl; ?>/assets/images/down/android_icon.png" alt="安卓">
					</a>
				</div>
				<!--二维码-->
				<div class="QRCode">
					<img src="<?= $qrcode?>">
				</div>
			</section>

		</div>

		<script src="<?= Yii::$app->request->baseUrl; ?>/assets/js/jquery-3.1.1.min.js"></script>
		<script>
			$(function() {
				if(isWeiXin()){
					$(".xinren").show();
				}

				$(".download").click(function() {
					if(is_ios()) {
						if(isWeiXin()){
							alert('请在右上角用浏览器打开下载！');
						}
						open("itms-services://?action=download-manifest&amp;url=<?= $model->plist ?>");
					} else if(is_android()) {
						if(isWeiXin()){
							alert('安卓手机请打开浏览器进行下载！');
						}
						open("<?= $model->apk ?>");
					}
				});
			})
		</script>
	</body>
</html>