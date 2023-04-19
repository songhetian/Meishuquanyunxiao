<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="keywords" content="美术圈 美术 艺术 艺考" />
    <meta name="author" content="美术圈" />
    <title><?php echo $enrollment_guide_list->name ?></title>
    <link rel="Shortcut Icon" href="<?php echo $enrollment_guide_list->thumbnails ?>" />
    <link rel="stylesheet" href="<?php echo Yii::$app->request->baseUrl; ?>/assets/index/share/css/headline.css">
    <script src="<?php echo Yii::$app->request->baseUrl; ?>/assets/index/share/js/jquery-2.1.1.js"></script>
    <style type="text/css">
    img{
        width: 100% !important;
        height:100% !important;
    }
    </style>    
</head>

<body><!-- 
    <?php  if($source == false): ?>
        <div class="borderLogo">
            <a href="<?php echo $down_url ?>">
                <img src="http://meishuquan.img-cn-beijing.aliyuncs.com/headimg-temp2.jpg" style="width: 100%;" onclick="openClick();" />
            </a>
        </div>
        <div style="height: 50px;"></div>
    <?php endif ?>  -->
    <div class="main fontSize2">
        <p class="title" align="left">
            <?php echo $enrollment_guide_list->name ?>
        </p>
        <span class="src">
            <?php echo date("Y-m-d H:i:s",$enrollment_guide_list->created_at); ?>&nbsp;&nbsp;<?php //echo @$enrollment_guide_list->studio_id ?>
        </span>
        <div class="text">
            <?php echo @$enrollment_guide_list->desc ?>
        </div>
    </div>
</body>
<script type="text/javascript">
        $(function(){
            var browser = {
                versions: function () {
                    var u = navigator.userAgent, app = navigator.appVersion;
                    return { //移动终端浏览器版本信息 
                        ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端 
                        android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器 
                        iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器 
                        iPad: u.indexOf('iPad') > -1, //是否iPad 
                    };
                }(),
            }
            
            if (browser.versions.iPhone || browser.versions.iPad || browser.versions.ios) {
               function setupWebViewJavascriptBridge(callback) {
                    if (window.WebViewJavascriptBridge) { return callback(WebViewJavascriptBridge); }
                    if (window.WVJBCallbacks) { return window.WVJBCallbacks.push(callback); }
                    window.WVJBCallbacks = [callback];
                    var WVJBIframe = document.createElement('iframe');
                    WVJBIframe.style.display = 'none';
                    WVJBIframe.src = 'wvjbscheme://__BRIDGE_LOADED__';
                    document.documentElement.appendChild(WVJBIframe);
                    setTimeout(function() { document.documentElement.removeChild(WVJBIframe) }, 0)
                }
                setupWebViewJavascriptBridge(function(bridge) {
                    $('img').click(function(){
                        src = $(this).attr('src');
                        $.get('get-imgsize',{src:src},function(data){
                            var json = eval('(' + data + ')'); 
                            bridge.callHandler('ClickImageAction', {
                                'imgURL': decodeURI(src),
                                'width':json.width,
                                'height':json.height
                            },
                            function(response) {
                                log('JS got response', response)
                            })
                        });
                        
                    });
                })
            }
            if (browser.versions.android) {
                var imgs = document.getElementsByTagName("img");
                for(var i=0;i<imgs.length;i++){
                    imgs[i].setAttribute('onclick',"demo.clickOnAndroid('"+imgs[i].src+"')");
                }
            }
        });
    </script>
</html>