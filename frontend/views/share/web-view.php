

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="keywords" content="美术圈 美术 艺术 艺考" />
    <meta name="author" content="美术圈" />
    <title>美术圈云校</title>
    <link rel="Shortcut Icon" href="<?php echo $logo->logo; ?>?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c" />
    <link rel="stylesheet" href="<?php echo Yii::$app->request->baseUrl; ?>/assets/index/share/css/headline.css">
    <script src="<?php echo Yii::$app->request->baseUrl; ?>/assets/index/share/js/jquery-2.1.1.js"></script>
    <script type="text/javascript">
</script>
</head>
    <style type="text/css">
    .open {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -ms-flex-negative: 0;
    flex-shrink: 0;
    color: #fff;
    border-radius: 1rem;
    background: #e31818;
    float: right;
    height: 30px;
    width: 60px;
    margin-top: 10px;margin-right: 15px;
}
    </style>    
</head>
 <?php if($_GET['is_banner']==1){ ?>
<div style='background-color:rgba(34,34,34,.8);height: 50px;width:100%;'>
    <a href="http://www.meishuquanyunxiao.com/download/index.html?token_value=<?php echo $studio->token_value; ?>">
        <div class="image"  style='float:left;height: 50px;width:55px;' >
            <img class="logo" src="<?php echo $logo->logo; ?>?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c" style="width: 40px;height: 40px;margin-top: 5px;margin-left: 15px;">
        </div>
        <div class="image"  style='float:left;height: 50px;line-height:50px;color:#fff;padding-left: 5px' >
            <?php echo $studio->name; ?>
        </div>
        <div class="open">下载</div>
    </a>
</div>
<?php } ?>
<body>
    <!-- <iframe src="<?php echo $url; ?>" id="iframe2" name="iframe2" width="100%" scrolling="yes" onload="this.height=document.body.clientHeight"  frameborder="0"  ></iframe> -->
    <object width="100%" type="text/x-scriptlet" data="<?php echo $url; ?>">
</body>
<script>
    document.getElementsByTagName('body')[0].style.height = window.innerHeight+'px';
</script>
</html>