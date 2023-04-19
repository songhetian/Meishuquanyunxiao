
<!DOCTYPE html>
<html lang="zh-CN" >
<head>
  <link rel="shortcut icon" type="image/x-icon" href="http://meishuquan.oss-cn-beijing.aliyuncs.com/headimg-temp.png" />
  <title><?php echo @$studio->name;?></title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="北京墨提斯科技有限公司">
  <meta name="keywords" content="美术圈">
  <meta name="description" content="美术圈，像看书一样看视频吧">
  
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-status-bar-style" content="black" />
  <meta name="format-detection" content="telephone=no">
  <meta name="screen-orientation" content="portrait">
  <meta name="x5-orientation" content="portrait">
  <meta name="browsermode" content="application">
  <meta name="x5-page-mode" content="app">
  <link rel="stylesheet" media="all" href="<?php echo Yii::$app->request->baseUrl; ?>/yangshi.css" />
</head>
<body>
  <!-- 放入head标签 start -->
<!-- 放入head标签 end -->
<div class="body-container">
  <!-- 页面头部 start -->
  <header>
  <img height="30px" class="logo" src="http://meishuquan.oss-cn-beijing.aliyuncs.com/headimg-temp.png" alt="Logo db2431ce8273a028486aa418c84d8813e3064210d767761f1a1be37936464fd7" />
<span style='line-height:45px;padding-left:50px;height: 45px;color:#000;display: block;font-weight: bold;font-size:18px;'><?php echo $live->description;?></span>
</header>

  <!-- 页面头部 end -->

  <!-- 页面主体区域  start-->
  <div class="notice_container">
  <!-- 视频区域 start-->
  	<div class="medias">
      <!-- 视频海报区域 start-->
      <div class="bg">
        <!-- 顶部状态区域 start-->
        <i class="label label0" ></i>
        <!-- 顶部状态区域 end-->

        <!-- 底部价格区域 start -->
          <div class="money">
              <span class="free"><?php echo @$live->authtype==2?'免费':'收费'?></span>
          </div>
        <!-- 底部价格区域 end -->

        <img class="bg" src="<?php echo $live->pic_url;?>" />
      </div>
      <!-- 视频中间的播放按钮 end -->

    </div>
    <!-- 视频区域 end-->

    <!-- 页面中间tab切换菜单栏  start-->
    	<div class="tabh1">
      	<div class="for_detail">
      		<div class="left show_detail current" onclick="hide_chat()">简介</div>
      		<a class="right back show_chat" onclick="show_chat()"></a>
      	</div>
    	</div>
    <!-- 活动的相关信息区域 start -->
    <div class="information">
      <!-- 活动标题 start-->
      <h1 class="emoji"><?php echo $live->description; ?></h1>
      <!-- 活动标题 end -->


      <!-- 倒计时和开播提醒 start -->
        <div class="clock">
        	距离直播还有
          <div class="countdown" id="countdown">
          <?php 
      if(strtotime($live->start_time) >= time()){
          $remain_time = strtotime($live->start_time)-time(); 
          $day = floor($remain_time / (3600*24));
          $day = $day > 0 ? $day.' 天 ' : '0 天 ';
          $hour = floor(($remain_time % (3600*24)) / 3600);
          $hour = $hour > 0 ? $hour.' 小时 ' : '0 小时 ';
          $minutes = floor((($remain_time % (3600*24)) % 3600) / 60);
          $minutes = $minutes > 0 ? $minutes.' 分 ' : '0 分 ';
          $second = floor((($remain_time % (3600*24)) % 3600) % 60 );
          $second = $second > 0 ? $second.' 秒 ' : '0 秒 ';

          if($second){
            echo  $day.$hour.$minutes.$second;
          }else if($minutes){
            echo  $day.$hour.$minutes;
          }else if($hour) {
            echo  $day.$hour;
          }else{
            echo  $day;
          }
      }else{
        echo "主播正在准备直播，请稍等····";
      }
          ?></div>
        </div>

      <!-- 主播 start -->
      <div class="info">
        <h5>讲师</h5>
        <div class="user_info row">
  <div class="avatar col-xs-2 link-to-homepage" >
      <div class="img" style="background-image: url(<?php echo $studio->image;?>)"></div>
  </div>
  <div class="details col-xs-10">
    <div class="nickname emoji">
      <?php echo $studio->name;?>
    </div>
    <div class="emoji">
       
    </div>
  </div>
</div>
      </div>
      <!-- 主播 end --><!-- 
      <div class="height60"></div> -->
    </div>
  </div>
</div>
<!-- 底部菜单 start -->
  <div class="bottom-menu-container"  onclick="javascript:window.location.href='<?php echo @$down_url ?>'" >
   <div class="bottom-menu-body">
        <img style="width:100%;height: 100%"  src='http://meishuquan.oss-cn-beijing.aliyuncs.com/headimg-temp3.jpg' style="width:100%;height: 100%" />
      <!-- </div> -->
    </div>
  </div>
<!-- 底部菜单 end -->
<!-- 页面主体区域  end-->
<!--footer>
  © 2015 juchang.tv
</footer-->


</body>

 <script language="javascript">
     var t = null;
     var sec = 1;
    t = setTimeout(time,1000);//开始执行
    function time()
    {
       clearTimeout(t);//清除定时器
       dt = <?php echo strtotime($live->start_time)-time()>=0?strtotime($live->start_time)-time():0;?>;
       dt -= sec;
       sec++;
       if(dt >= 0){
         var day = parseInt(dt / (3600*24));
         var hour = parseInt((dt % (3600*24)) / 3600);
         var minutes = parseInt(((dt % (3600*24)) % 3600) / 60);
         var second = parseInt(((dt % (3600*24)) % 3600) % 60);
         document.getElementById("countdown").innerHTML =  ""+day+" 天 "+hour+" 时 "+minutes+" 分 "+second+" 秒 ";
         t = setTimeout(time,1000); //设定定时器，循环执行
       }else{
        document.getElementById("countdown").innerHTML =  "主播正在准备直播，请稍等····";
       }
    } 
  </script>
</html>