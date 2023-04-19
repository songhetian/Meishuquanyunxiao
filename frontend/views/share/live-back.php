<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo @$live->title;?></title>
    <link rel="stylesheet" type="text/css" href="//view.csslcloud.net/css/main_mobile.css"/>
    <style>
        #playbackPlayer{height: 100%;}
        video::-webkit-media-controls-fullscreen-button {
            display: none;
        }
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

                    <!-- 回放视频模块 -->
                    <div id="playbackPlayer"></div>

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
        </div>
        <div class="slider-container">
            <div class="container chatBox">
                <div class="slider-bd allow-roll">
                    <ul id="chat_container" class="msg-list"></ul>
                </div>
               
            </div>
        </div>
    </div>
</div>
<input id="barrage" type="hidden" value="0"/>
<script src="//view.csslcloud.net/js/jquery-1.9.0.min.js?v=<?php echo time();?>"></script>
<script src="//view.csslcloud.net/js/touchSlide.js?v=<?php echo time();?>"></script>
<script src="//view.csslcloud.net/js/main.js?v=<?php echo time();?>"></script>
<script src="//view.csslcloud.net/js/jquery.panzoom.min.js?v=<?php echo time();?>"></script>
<script src="//view.csslcloud.net/js/playbackSDK.js?v=<?php echo time();?>"></script>
<script type="text/javascript">

    // 回放SDK参数配置
    $.DW.config({
        userId: '8FF243A3955D1B18',
        roomId: '<?php echo @$live->cc_id;?>',
        recordId: '<?php echo @$live->record_id;?>',
        viewername: '游客<?php echo time();?> ',
    });

    // 接收聊天信息
   function on_cc_live_chat_msg(data) {
       console.log(data);
       var msg = data.msg;
       var name = data.username, msgStr = showEm(msg);
       var liEl = $('<li>');
       liEl.append($('<p>').text(name + "：").append(
               $('<span>').html(msgStr).addClass("tmsg")));
       $('#chat_container').append(liEl);
   }

    // 同步接收聊天信息
    function on_cc_live_chat_msg_sync(datas) {
        console.log('on_cc_live_chat_msg_sync', datas);
        var mh = '';

        for (var idx = 0; idx < datas.length; idx++) {
            var msg = datas[idx];

            var name = msg['username'], msgStr = showEm(msg['msg'] || '');

            var liEl = '<li><p>' + name + '：<span class="tmsg">' + msgStr + ' </span></p></li>';

            mh += liEl;
        }

        var messageCount = $('#chat_container').children().length;
        var overCount = messageCount - 1000 + datas.length;
        if (overCount > 0) {
            $('#chat_container > div:lt(' + overCount + ')').remove();
        }

        $('#chat_container').append(mh);

        $('#chat_container').parent().scrollTop($('#chat_container').height());
    }

    function showEm(str) {
        if (!$.trim(str)) {
            return '';
        }
        str = str.replace(/\</g, '&lt;');
        str = str.replace(/\>/g, '&gt;');
        str = str.replace(/\n/g, '<br/>');
        str = str.replace(/\[em_([0-9]*)\]/g, '<img src="/img/em/$1.gif" border="0" />');
        str = str.replace(/\[em2_([0-9]*)\]/g, '<img src="/img/em2/$1.png" border="0" />');

        var nmsg = '';
        $.each(str.split(' '), function (i, n) {
            n = $.trim(n);
            if (n.indexOf('[uri_') == 0 && n.indexOf(']') == n.length - 1 && n.length > 6) {
                var u = n.substring(5, n.length - 1) + ' ';
                nmsg += '<a target="_blank" style="color: #2f53ff" href="' + u + '">' + u + '</a>' + ' ';
            } else {
                nmsg += n + ' ';
            }
        });

        return nmsg;
    }

    function removeEm(str) {
        return str.replace(/\[em2?_([0-9]*)\]/g, '');
    }

    setTimeout(function () {
        $("#topHalf").height($(window).height() - $("#bottomHalf").height());
    }, 1500);

    // 接收回答
    function on_cc_live_qa_answer(j) {
        if (!j) {
            return;
        }
        if (j.action !== 'answer') {
            return;
        }
        var v = j.value;
        if (!v) {
            return;
        }
        var qid = v.questionId,
                qc = v.content,
                quid = v.userId,
                quname = v.userName,
                isPrivate = v.isPrivate;
        if (isPrivate) {
            return;
        }
        if (!$('#questionInfo').length) {
            return;
        }

        var q = $('#questionInfo #' + qid);
        if (!q.length) {
            $('#questionInfo').append('<li id="' + qid + '"></li>');
            q = $('#questionInfo #' + qid);
        }
//        q.show();
        q.append('<p class="qaanswer"><span class="answername">' + $.escapeHTML(quname) + ' 答：</span><span class="answermsg">' + $.escapeHTML(qc) + '</span></p>');
        $('#questionInfo').scrollTop($('#questionInfo').height());
    }

    // 接收提问
    function on_cc_live_qa_question(j) {
        if (!j) {
            return;
        }
        if (j.action !== 'question') {
            return;
        }
        var v = j.value;
        if (!v || (v.isPublish != 1)) {
            return;
        }
        var qid = v.id,
                qc = v.content,
                quid = v.userId,
                quname = v.userName;
        if (!$('#questionInfo').length) {
            return;
        }

        var q = $('#questionInfo #' + qid);
        if (!q.length) {
            $('#questionInfo').append('<li id="' + qid + '"></li>');
            q = $('#questionInfo #' + qid);
        }
        q.append('<p class="qaask"><span class="askname">' + $.escapeHTML(quname) + ' 问：</span><span class="askmsg">' + $.escapeHTML(qc) + '</span></p>');

        $('#questionInfo').scrollTop($('#questionInfo').height());
    }
</script>
</body>
</html>