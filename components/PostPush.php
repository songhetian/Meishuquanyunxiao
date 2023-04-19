<?php 
	namespace components;
	use Yii;
	use components\Push;

	class PostPush {

		/**
		 * 推送方法
		 * 参数
		 * plats Integer[]	 可使用平台，1、android ； 2、ios ；如包含ios和android则为[1,2]
		 * target int 推送范围:1广播；2别名；3标签；4regid；5地理位置;6用户分群
		 * content String 推送类型 
		 * type  int 推送类型：1通知；2自定义 
		 * androidContent stirng[] androidstyle样式具体内容：
		 *								0、默认通知无；
		 *								1、长内容则为内容数据；
		 *								2、大图则为图片地址；
		 *								3、横幅则为多行内容
		 * androidstyle int  Android显示样式标识normal(0,”普通通知”),
		 *					bigtext(1,”BigTextStyle通知，点击后显示大段文字内容”)
		 *					bigpicture(2,”BigPictureStyle，大图模式”),
		 *					hangup(3,”横幅（收件箱）通知”);
		 * androidTitle  string Android显示标题
		 *
		 * scheme    srting 传的url地址 针对电子书 课程 需要跳转的      
		 *
		 */
		public static function PushMsg($content,$androidContent,$androidTitle,$pushtype,$id,$scheme = '',$androidstyle=0,$target=1,$type=1,$cc_id = "" ) {
			//'2a95ad1e3ed9a','fe31c91f023f17cfd0ea960f887fdbe1'  正式
			//'2d53ea1b1081a','cbd3d60b24bbd4670779bc9d698128e7'   测试
	        $push = new Push('2a95ad1e3ed9a','fe31c91f023f17cfd0ea960f887fdbe1');

	       // var_dump(json_encode(array('turn_type'=>'news','turn_id'=>100,'url'=>'https://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id=100')));exit;

	        $PushConfig = array(
            'appkey' => '2a95ad1e3ed9a',
	            'plats'  => [1,2],
	            'target' => $target,
	            'content' => $content,
	            'title' => $androidTitle,
	            //'iosTitle' => $androidTitle,
	            //'iosSubtitle' => $androidTitle,
	            //'subtitle' => $androidTitle,
	            //'iosMutableContent' => $scheme,
	            'type'    => $type,
	            // 'scheme'  => 'https://api.teacher.meishuquanyunxiao.com/v2/ebook/get-ebook-info',
	            // 'data'    => array('value'=>594),
	            // "extras"    => array('key' => "https://api.teacher.meishuquanyunxiao.com/v2/ebook/get-ebook-info",'value'=>594), 
	            'iosProduction' => 0,
	            "androidContent" => [$androidContent],
	            'androidstyle'  => $androidstyle,
	            "androidTitle"=>    $androidTitle,
	            //'extras' => "{'turn_type':'news','turn_id':100,'url':'https:\/\/www.meishuquanyunxiao.com\/share\/new-list-view.html?new_list_id=100'}",
	            'extras'      =>    array('type'=>$pushtype,'turn_id'=>$id,'url'=>$scheme,'cc_id'=>$cc_id),
	            'data' =>   array('type'=>$pushtype,'turn_id'=>$id,'url'=>$scheme,'cc_id'=>$cc_id),
	        );

	        if($scheme) {
	        	$PushConfig['scheme'] = $scheme;
	        }
	        return $push->postPush($PushConfig,1);

		}
	}
 ?>