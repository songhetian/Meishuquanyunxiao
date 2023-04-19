<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo @$live->title;?></title>
    <link rel="stylesheet" type="text/css" href="http://view.csslcloud.net/css/mobile.css?v1=<?php echo time();?>"/>
    <link rel="stylesheet" type="text/css" href="http://view.csslcloud.net/css/style.css?v=<?php echo time();?>"/>
    <style>
        #livePlayer{height: 100%;}
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
<body>
<div id="webPlayer" class="web">
    <div id="topHalf" class="section-top">
        <div id="dispArea" class="disp-area">
            <div id="video-box" class="video-container upper">

                <div class="video-box">

                    <!-- 直播视频模块 -->
                    <div id="livePlayer"></div>

                </div>
            </div>
        </div>
        <div style="display:none;" class="waiting-layer">
            <div>直播未开始</div>
        </div>
    </div>

    <div id="bottomHalf" class="section-bottom">
        <div class="tabs" style="height:50px;">
<div style='background-color:rgba(34,34,34,.8);height: 50px;width:100%;'>
    <a href="https://a.app.qq.com/o/simple.jsp?pkgname=com.meishuquanyunxiao.artworld">
        <div class="image"  style='float:left;height: 50px;width:55px;' >
            <img class="logo" src="<?php echo $logo->logo; ?>?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c" style="width: 40px;height: 40px;margin-top: 5px;margin-left: 15px;">
        </div>
        <div class="image"  style='float:left;height: 50px;line-height:50px;color:#fff;padding-left: 5px' >
            <?php echo $studio->name; ?>
        </div>
        <div class="open">下载</div>
    </a>
</div>
        </div>
        <div class="slider-container">
            <div class="container chatBox">
                <div class="slider-bd allow-roll">
                    <ul id="chat_container" class="msg-list"></ul>
                </div>
                <div class="slider-ft chat-submit">
                    <a class="btn-phiz embtn" href="javascript:void(0);"><span class="icon-phiz"></span></a>
                    <a href="javascript:void(0);" class="chatlistbtn" for="all" id="chatlistbtn"></a>
                    <div class="chat-edit-area">
                        <input type="text" id="chat_input" placeholder="公聊模式,您的发言所有人可见" class="chat_input"/>
                    </div>
                    <button id="btn-chat-submit" onclick="chatSend();" class="submit-btn" type="submit">发送</button>
                    <div class="submit-tips" id="alert_container">
                        <strong>您已经被禁言您已经被禁言</strong><em>×</em>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="barrage" type="hidden" value="0"/>
<script src="http://view.csslcloud.net/js/jquery-1.9.0.min.js?v=<?php echo time();?>"></script>
<script src="http://view.csslcloud.net/js/touchSlide.js?v=<?php echo time();?>"></script>
<script src="http://view.csslcloud.net/js/jquery.panzoom.min.js?v=<?php echo time();?>"></script>
<script src="http://view.csslcloud.net/js/main.js"></script>
<script src="http://view.csslcloud.net/js/liveSDK.js?v=<?php echo time();?>"></script>
<script src="<?php echo Yii::$app->request->baseUrl; ?>/assets/index/share/cclive/js/chat_qa.js"></script>
<script type="text/javascript">

    $(function () {

        // 直播SDK参数配置
        DWLive.init({
            userid: '8FF243A3955D1B18',
            roomid: '<?php echo @$live->cc_id;?>',
            viewername: '游客<?php echo time();?>'
        });


        $(".embtn").bind('touchend', function (e) {
            if ($('#embox').length > 0) {
                $('#embox').hide().remove();
            } else {
                var strFace;
                var path = 'http://view.csslcloud.net/img/em2_mobile/';
                if ($('#embox').length <= 0) {
                    strFace = '<div id="embox" style="position:absolute;z-index:1000;bottom:39px;left:0;">' +
                            '<table border="0" cellspacing="0" cellpadding="0"><tr>';
                    for (var i = 1; i <= 20; i++) {
                        strFace += '<td><img src="' + path + handleEm(i) + '.png" ontouchend="setEm(' + handleEm(i) + ')" /></td>';
                        if (i % 10 == 0) strFace += '</tr>';
                    }
                    strFace += '</table></div>';
                }
                $('.chatBox').append(strFace);

                e.stopPropagation();
            }
        });
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
                    imgs [i].setAttribute('onclick',"demo.clickOnAndroid('"+imgs[i].src+"')");
                }
            }
    });
    function handleEm(i) {
        if (i < 10) {
            return '0' + i;
        }
        return i;
    }

    function setEm(e) {
        var emstr = '[em2_' + handleEm(e) + ']';
        $('#embox').hide().remove();
        $('#chat_input').val(function () {
            return $(this).val() + emstr;
        });
    }

    $(document).bind('touchend', function () {
        while ($('#embox').length > 0) {
            $('#embox').hide().remove();
        }
    });

    window.onbeforeunload = function (e) {
        if (window.LivePlayer && window.LivePlayer.isPublishing) {
            return "您确定要离开直播间吗？";
        } else {
            if (!window.event) {
                return null;
            }
        }
    };
</script>
</body>
</html>