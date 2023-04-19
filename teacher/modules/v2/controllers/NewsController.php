<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\Campus;
use common\models\News;
use common\models\Daily;
use common\models\DailyComment;
use common\models\UserFollow;
use common\models\User;
use common\models\UserLike;
use components\Oss;
use teacher\modules\v1\models\Admin;
use components\Upload;

class NewsController extends MainController
{
  public $modelClass = 'common\models\News';
  public function actionGetName($user_id=0){

    $size = Yii::$app->params['oss']['Size']['original'];
    $news = News::findAll(['status'=>1]);
    foreach ($news as $key => $value) {
      $news[$key]['icon'] = Oss::getIcon($value['icon']).$size;
    }

    return [
            'success' => true,
            'data' => $news,
            'message' => '获取数据成功'
        ];
  }



	public function actionGetList()
  {
    if(!empty(Yii::$app->request->post('name')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) ){
      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;
      switch (Yii::$app->request->post('name'))
      {
      case 'XinWenZiXun':
              $pic3 = array(array('url'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010184856_94765.jpg'),array('url'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010191330_91150.jpg'),array('url'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010191926_49391.jpg'));
              $pic2 = array(array('url'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010184856_94765.jpg'),array('url'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010191330_91150.jpg'));
              $pic1 = array(array('url'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010184856_94765.jpg'));
          for ($i=1; $i <= $limit; $i++) {
              $str = 'pic'.rand(1,3);
              $XinWenZiXun['list'][] = array(
                                 'name'=>'深秋十月 | “精微”--'.($i+$offset),
                                 'url'=>'https://www.airbnbchina.cn/content/stories/10343?_branch_match_id=456688146066188743',
                                 'id'=>$i+$offset,
                                 'thumbnails'=>$$str,
                                  'studio_name'=>'云校',
                                  'created_at'=>'1506065766591',
                                  'update_at'=>'1506065766519',
                                  'is_top' => 1,
                                  'share'=> array(
                                          'share_title'=>'深秋十月 | “精微”--'.($i+$offset),
                                          'share_desc'=>'wa wa wa ',
                                          'share_url'=>'www.baidu.com',
                                          'share_image'=>'http://www.boyihuashi.com/uploads/image/20171010/20171010184856_94765.jpg',
                                        )
                                  );
          }
          return [
              'success' => true,
              'data' => $XinWenZiXun,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
          case 'HuiHuangChengJi':
          for ($i=0; $i <= $limit; $i++) { 
              $HuiHuangChengJi['list'][] = array(
                                 'id'=>$i+1,
                                 'name'=>'2017年北京博艺画室专业成绩',
                                 'Author'=>'FUKCUP',
                                 'url'=>'http://www.boyihuashi.com/html/lncj.html',
                                 'Thumbnails'=>'http://www.boyihuashi.com/uploads/171030/1-1G0301I052441.jpg',
                                 'studioName'=>'云校',
                                 'addTime'=>'1506065766591',
                                 'UpTime'=>'1506065466599',
                                 'Video'=>array(
                                     'isVideo'=>'true',
                                     'VideoTime'=>'01:05:21',
                                     'coverPic'=>'http://c.vpimg1.com/upcb/2017/09/22/119/ias_150606937687943_570x273_90.jpg'
                                 ));
          }
          return [
              'success' => true,
              'data' => $HuiHuangChengJi,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
          case 'JiaoShiTuanDui':
          for ($i=0; $i <= $limit; $i++) { 
              $JiaoShiTuanDui['list'][] = array(
                                 'name'=>'Jack',
                                 'role'=>'校长',
                                 'id'=>$i+1,
                                 'url'=>'https://m.huxiu.com/article/213921.html',
                                 'TouXiangpic'=>'https://goods1.juancdn.com/goods/170908/1/2/59b203cea9fcf86c475a60ba_800x800.jpg'
                             );
          }
          return [
              'success' => true,
              'data' => $JiaoShiTuanDui,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
          case 'YouXiuZuoPin':
          for ($i=0; $i <= $limit; $i++) {
              $YouXiuZuoPin['list'][] = array(
                                  'data'=> array(
                                     array('name'=>'大头1',
                                             'id'=>$i+1,
                                             'url'=>'http://www.boyihuashi.com/uploads/allimg/160425/1-160425233R40-L.jpg'
                                         ),
                                     array('name'=>'大头2',
                                             'id'=>$i+6,
                                             'url'=>'http://www.boyihuashi.com/uploads/allimg/160425/1-160425233R40-L.jpg'
                                         ),
                                     array('name'=>'大头3',
                                             'id'=>$i+11,
                                             'url'=>'http://www.boyihuashi.com/uploads/allimg/160425/1-160425233R40-L.jpg'
                                         ),
                                     array('name'=>'大头4',
                                             'id'=>$i+16,
                                             'url'=>'http://www.boyihuashi.com/uploads/allimg/160425/1-160425233R40-L.jpg'
                                         ),
                                     ),
                                  'name'=> '设计作品'
                             );
          }
          return [
              'success' => true,
              'data' => $YouXiuZuoPin,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
          case 'ZhiBo':
          for ($i=0; $i <= $limit; $i++) { 
              $ZhiBo['list'][] = array(
                                 'name'=>'直播1',
                                 'url'=>'https://m.huxiu.com/article/213921.html'
                             );
          }
          return [
              'success' => true,
              'data' => $ZhiBo,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
          case 'YuanXiaoBaoKao':
          $YuanXiaoBaoKao['list'][] = array(
                              '重点省份'=> array(
                                  '北京' => array(
                                     array("name"=>"北京市美术联考","renqi"=>888888,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                     array("name"=>"北京美术联考","renqi"=>1234,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                     array("name"=>"美术联考","renqi"=>4567,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                  ),
                                  '重庆' => array(
                                     array("name"=>"重庆市美术联考","renqi"=>888888,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                     array("name"=>"重庆美术联考","renqi"=>1234,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                     array("name"=>"美术联考","renqi"=>4567,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                  ),
                                  '河北' => array(
                                     array("name"=>"河北市美术联考","renqi"=>888888,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                     array("name"=>"河北美术联考","renqi"=>1234,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                     array("name"=>"美术联考","renqi"=>4567,"pic"=>'http://r.iviedu.com/Files/image/2017615/170629144155.png'),
                                  ),
                              ),
                              '综合推荐' => array(
                                  "全国联考"=>array(
                                     array('id'=>1,"name"=>"山东省美术联考","renqi"=>886666,"pic"=>'https://goods5.juancdn.com/bao/170629/8/f/5954fa428150a16dd215f49f_800x800.jpg'),
                                 ),
                                 "九大美院"=>array(
                                     array('id'=>1,"name"=>"山东省美术联考","renqi"=>1234,"pic"=>'https://goods5.juancdn.com/bao/170629/8/f/5954fa428150a16dd215f49f_800x800.jpg'),
                                 ),
                                 "13所院校"=>array(
                                     array('id'=>1,"name"=>"山东省美术联考","renqi"=>5678,"pic"=>'https://goods5.juancdn.com/bao/170629/8/f/5954fa428150a16dd215f49f_800x800.jpg'),
                                 ),
                                 "31所院校"=>array(
                                     array('id'=>1,"name"=>"山东省美术联考","renqi"=>9123,"pic"=>'https://goods5.juancdn.com/bao/170629/8/f/5954fa428150a16dd215f49f_800x800.jpg'),
                                 ),
                              )
                         );
          return [
              'success' => true,
              'data' => $YuanXiaoBaoKao,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
        case 'ZhaoshengJianZhang':
          $ZhaoshengJianZhang['list'][0] = array(
                               'XiaoYuanHuanJing'=>'true',
                               'name'=>' 校园环境',
                               'id'=>10,
                               'Author'=>'FUKCUP',
                               'url'=>'http://m.baidu.com',
                               'Thumbnails'=>'http://www.boyihuashi.com/uploads/171030/1-1G0301I052441.jpg',
                               'studioName'=>'云校',
                               'addTime'=>'1506065766591',
                               'UpTime'=>'1506065466599',
                              'coverPic'=>'http://c.vpimg.com/upcb/2017/09/22/119/ias_150606937687943_570x273_90.jpg'
                         );
          $ZhaoshengJianZhang['list'][1] = array(
                                 'name'=>' 招生咨询',
                                 'id'=>11,
                                 'Author'=>'FUKCUP',
                                 'url'=>'http://m.baidu.com',
                                 'Thumbnails'=>'http://www.boyihuashi.com/uploads/171030/1-1G0301I052441.jpg',
                                 'studioName'=>'云校',
                                 'addTime'=>'1506065700591',
                                 'UpTime'=>'1506065466599',
                                 'coverPic'=>'http://c.vpimg.com/upcb/2017/09/22/119/ias_150606937687943_570x273_90.jpg',
                                 'ZhaoshengZixun'=>array(
                                      'ZhaoshengZixun'=>'true',
                                      'name'=>'招生咨询',
                                      'list'=>array(
                                          array('id'=>1,
                                          'name'=>'2017年北京博艺画室专业成绩',
                                          'Author'=>'FUKCUP',
                                          'url'=>'http://www.boyihuashi.com/html/lncj.html',
                                          'Thumbnails'=>'http://www.boyihuashi.com/uploads/171030/1-1G0301I052441.jpg',
                                          'studioName'=>'云校',
                                          'addTime'=>'1506065766591',
                                          'UpTime'=>'1506065466599',
                                          'Video'=>array(
                                                  'isVideo'=>'true',
                                                  'VideoTime'=>'01:05:21',
                                                  'coverPic'=>'http://c.vpimg1.com/upcb/2017/09/22/119/ias_150606937687943_570x273_90.jpg'
                                              )
                                          ),
                                      )
                                  )
                         );
          $ZhaoshengJianZhang['list'][2] = array(
                                 'name'=>'抢宿舍',
                                 'url'=>'http://m.baidu.com',
                                 'id'=>7,
                                 'Thumbnails'=>'http://www.boyihuashi.com/uploads/171030/1-1G030155334557.jpg',
                                 'studioName'=>'云校',
                                 'Author'=>'FUKCUP',
                                 'addTime'=>'1506065766591',
                                 'UpTime'=>'1506065761599'
                         );
          return [
              'success' => true,
              'data' => $ZhaoshengJianZhang,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
          case 'BaoMingZiXun':
          for ($i=0; $i <= $limit; $i++) { 
                $BaoMingZiXun[$i] = array(
                                   'name'=>'报名咨询'.$i,'url'=>'https://m.huxiu.com/article/213921.html'
                               );
          }
          return [
              'success' => true,
              'data' => $BaoMingZiXun,
              'message' => '获取数据成功'
          ];break;
  //----------------------------------------------------------------------------------------------------------------------------------------------
        }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //发布动态
  public function actionAddDaily(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('content'))){
      $daily = new Daily();
      $daily->user_type = Yii::$app->request->post('user_type');
      $daily->user_id = Yii::$app->request->post('user_id');
      $daily->content = Yii::$app->request->post('content');

      $image_url_came = "";
      if(!empty(Yii::$app->request->post('image_url_came'))){
        $img = Yii::$app->request->post('image_url_came');
        foreach ($img as $key => $value) {
          $image_url_came .= ",".$value;
        }
        $image_url_came = substr($image_url_came,1,strlen($image_url_came));
        $daily->image_url_came = $image_url_came;
      }
      $daily->timer = time();
      if(Yii::$app->request->post('user_type')=='admin'){
        $daily->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else{
        $daily->studio_id= Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }
      
      if($daily->save()){
        return [
              'success' => true,
              'message' => '获取数据成功'
          ];
      }else{
        return [
            'success' => false,
            'message' => '服务器原因上传失败'
        ];
      }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }


  //获取动态
  public function actionGetDaily(){
    if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type'))){
      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;
      if(Yii::$app->request->post('user_type')=='admin'){
        $studio_id = Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else{
        $studio_id = Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }
      $daily = Daily::find()->where(['status'=>1,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('daily_id desc')->all();
      $res = array();
      foreach ($daily as $key => $value) {
        $image_url_came = array();
        if($value->user_type =='admin'){
          $user = Admin::findOne($value->user_id);
        }else{
          $user = User::findOne($value->user_id);
        }


        if(!empty($value->image_url_came)){
          $arr = explode(',',$value->image_url_came);
          foreach ($arr as $k => $v) {
            $image_url_came[$k]['url'] = $v;
          }
        }
        //关注相关数据
        $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'follow_user_id'=>$value->user_id,'follow_user_type'=>$value->user_type,'status'=>1]);
        $follow_num = UserFollow::find()->where(['follow_user_id'=>$value->user_id,'follow_user_type'=>$value->user_type,'status'=>1])->count();

        //点赞相关数据
        $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1]);
        $like_num = UserLike::find()->where(['like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1])->count();

        //数据整理
        $res[$key] = array(
            'id'=> $value->daily_id, 
            'avatar' => 'http://meishuquan.img-cn-beijing.aliyuncs.com/temp1/temp/71d3fa2ba630c8e8a6abb122d09c11c3.jpg',
            'name' => $user->name,
            'timer' => $value->timer*1000,
            'follow_user_id' => $value->user_id,
            'addres' => '',
            'status' => $value->user_type == 'admin'?'老师':'学生',
            'follow_user_type' => $value->user_type,
            'is_follow' => !empty($is_follow)?true:false,//是否关注
            'follow' => (int)$follow_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'is_activating' => rand(0,1)==1?true:false,//教师是否激活
            'content'=> $value->content,//文字
            'image_url_came' => $image_url_came,//图片
            'views' => $value->views,//查看数
            'comment'=> (int)DailyComment::find()->where(['status'=>1,'daily_id'=>$value->daily_id])->count(1),//评论数
            'like' => (int)$like_num,//点赞
            'share'=> array(
              'share_title'=>'云校分享',
              'share_desc'=>'wa wa wa ',
              'share_url'=>'www.baidu.com',
              'share_image'=>'http://img1.imgtn.bdimg.com/it/u=2764371306,3467823016&fm=214&gp=0.jpg',
            )
        );
      }
      return [
                'success' => true,
                'data' => $res,
                'message' => '获取数据成功'
            ];
    }else{
      return [
        'success' => false,
        'message' => '非POST传值或缺少参数'
        ];
    }
    
  }

  //点赞动态或评论
  public function actionAddLike(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('like_type')) && !empty(Yii::$app->request->post('id'))){
      $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>Yii::$app->request->post('like_type'),'like_id'=>Yii::$app->request->post('id')]);
      if(!empty($is_like)){
        $is_like->status==1?$is_like->status=2:$is_like->status=1;
          if($is_like->save()){
          return [
                'success' => true,
                'message' => '成功'
            ];
        }else{
          return [
              'success' => false,
              'message' => '失败'
          ];
        }
      }else{
        $user_like = new UserLike();
        $user_like->user_type = Yii::$app->request->post('user_type');
        $user_like->user_id = Yii::$app->request->post('user_id');
        $user_like->like_type = Yii::$app->request->post('like_type');
        $user_like->like_id = Yii::$app->request->post('id');

        $user_like->timer = time();
        if(Yii::$app->request->post('user_type')=='admin'){
          $user_like->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        }else{
          $user_like->studio_id= Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        }
        if($user_like->save()){
          return [
                'success' => true,
                'message' => '点赞成功'
            ];
        }else{
          return [
              'success' => false,
              'message' => '点赞失败'
          ];
        }
      }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }
  //关注老师或学生
  public function actionAddFollow(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('like_type')) && !empty(Yii::$app->request->post('id'))){
      if(Yii::$app->request->post('user_type')=='admin'){
        $daily_comment->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else{
        $daily_comment->studio_id= Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //发表评论
  public function actionAddDailyComment(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('content')) && !empty(Yii::$app->request->post('id'))){
      $daily_comment = new DailyComment();
      $daily_comment->user_type = Yii::$app->request->post('user_type');
      $daily_comment->user_id = Yii::$app->request->post('user_id');
      $daily_comment->content = Yii::$app->request->post('content');
      $daily_comment->daily_id = Yii::$app->request->post('id');

      if(!empty(Yii::$app->request->post('daily_comment_pid'))){
        $daily_comment->daily_comment_pid = Yii::$app->request->post('daily_comment_pid');
      }
      $daily_comment->timer = time();
      if(Yii::$app->request->post('user_type')=='admin'){
        $daily_comment->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else{
        $daily_comment->studio_id= Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }
      
      if($daily_comment->save()){
        return [
              'success' => true,
              'message' => '发布评论成功'
          ];
      }else{
        return [
            'success' => false,
            'message' => '服务器原因上传失败'
        ];
      }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //获取评论
  public function actionGetDailyComment(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('id'))){

      if(Yii::$app->request->post('user_type')=='admin'){
        $studio_id = Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else{
        $studio_id = Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }
      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;

      $comment = DailyComment::find()->where(['status'=>1,'studio_id'=>$studio_id,'daily_id'=>Yii::$app->request->post('id'),'daily_comment_pid'=>0])->offset($offset)->limit($limit)->orderby('(case when user_id='.Yii::$app->request->post('user_id').' then 1 else 0 end) DESC, daily_comment_id DESC')->all();
      $res = array();
      if(!empty($comment)){
        foreach ($comment as $key => $value) {
          if($value->user_type == 'admin'){
            $user = Admin::findOne($value->user_id);
          }else{
            $user = User::findOne($value->user_id);
          }
          $rescomment = array();
          $this->getcomment($value->daily_id,$value->studio_id,$value->daily_comment_id,$rescomment,'',Yii::$app->request->post('user_id'),Yii::$app->request->post('user_type'));

          $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1]);
          $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1])->count();
          $res[$key] =  array(
            'id' => $value->daily_comment_id,
            'user_id' => $user->id,
            'like_num' => (int)$like_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'name' => $user->name,
            'avatar' =>'http://meishuquan.img-cn-beijing.aliyuncs.com/temp1/temp/71d3fa2ba630c8e8a6abb122d09c11c3.jpg',
            'timer' => $value->timer,
            'content' => $value->content,
            'reply_comment' => $rescomment,
          );
        }
      }
      return [
                'success' => true,
                'data' => $res,
                'message' => '获取数据成功'
            ];
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //获取下级评论
  public function getcomment($daily_id , $studio_id , $daily_comment_id , &$rescomment, $pidname = '',$user_id,$user_type){
    $comment = DailyComment::find()->where(['status'=>1,'studio_id'=>$studio_id,'daily_id'=>$daily_id,'daily_comment_pid'=>$daily_comment_id])->orderby('daily_comment_id DESC')->all();
    if(!empty($comment)){
      foreach ($comment as $key => $value) {
        if($value->user_type == 'admin'){
          $user = Admin::findOne($value->user_id);
        }else{
          $user = User::findOne($value->user_id);
        }
        $is_like = UserLike::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1]);
          $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1])->count();
        $rescomment[] =  array(
          'id' => $value->daily_comment_id,
          'user_id' => $user->id,
          'is_like' => !empty($is_like)?true:false,
          'like_num' => (int)$like_num,
          'name' => $user->name,
          'avatar' =>'http://meishuquan.img-cn-beijing.aliyuncs.com/temp1/temp/71d3fa2ba630c8e8a6abb122d09c11c3.jpg',
          'timer' => $value->timer,
          'content' => $value->content,
          'reviewers' => $pidname,
        );
        $this->getcomment($value->daily_id,$value->studio_id,$value->daily_comment_id,$rescomment,$user->name,$user_id,$user_type);
      }
      return $rescomment;
    }else{
      return array();
    }
  }


    //上传图片 || 语音
    public function actionUploadImageYun(){
      if(!empty($_FILES) && !empty($_GET['source'])){
        $image = Upload::pic_upload($_FILES,0,$_GET['source'], 'image');

        $data = $_FILES['file'];

        $data['url'] =  OSS::getUrl(0,$_GET['source'],'image',$image['file']).Yii::$app->params['oss']['Size']['original'];
        if(!empty($_POST)){
          foreach ($_POST as $key => $value) {
            $data['post_'.$key] = is_numeric($value)?(int)$value:$value;
          }
        }
        if(!empty($_GET)){
          foreach ($_GET as $key => $value) {
            $data['get_'.$key] = $value;
          }
        }
        return [
              'success' => true,
              'data' => $data,
              'message' => '上传成功'
          ];
      }else{
        return [
                'success' => false,
                'data' =>array(),
                'message' => '缺少参数'
            ];
      }
    }

}