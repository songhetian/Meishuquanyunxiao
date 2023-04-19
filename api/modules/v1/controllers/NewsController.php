<?php
namespace api\modules\v1\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Campus;
use common\models\News;
use common\models\PrizeList;
use common\models\TeacherList;
use common\models\WorksList;
use common\models\NewList;
use common\models\Prointroduction;
use common\models\EnrollmentGuideList;
use common\models\SchoolPic;
use common\models\Curl;
use common\models\Daily;
use common\models\Studio;
use common\models\DailyComment;
use common\models\StudioPower;
use common\models\UserFollow;
use common\models\User;
use common\models\Family;
use common\models\UserLike;
use common\models\ActivationCode;
use common\models\Registration;
use common\models\Format;
use common\models\RegistrationUser;
use components\Oss;
use teacher\modules\v1\models\Admin;
use components\Push;
use components\PostPush;
class NewsController extends MainController
{
  public $modelClass = 'common\models\News';
  //权限验证
  public function is_power($user_id,$user_type,$power_type,$studio_id){
    $power = StudioPower::findOne(['studio_id'=>$studio_id]);
    if(!empty($power)){
      switch ($user_type) {
        case 'student':
          if(!empty(User::findOne(Yii::$app->request->post('user_id'))->campus_id)){
            return $this->studentpower($power_type,$power);
          }else{
            return $this->peoplepower($power_type,$power);
          }
          break;
        case 'family':
          return $this->peoplepower($power_type,$power);
          break;
        default:
          return true;
          break;
      }
    }else{
      return true;
    }
  }

  public function peoplepower($power_type,$power){
     switch ($power_type) {
        case 'add_daily':
          if($power->people_add_daily == 10){
            return true;
          }else{
            return false;
          };
          break;
        case 'comment_daily':
          if($power->people_comment_daily == 10){
            return true;
          }else{
            return false;
          };
          break;
        case 'look_daily':
          if($power->people_look_daily == 10){
            return true;
          }else{
            return false;
          };
          break;
        default:
          return true;
          break;
      }
  }
  public function studentpower($power_type,$power){
   switch ($power_type) {
      case 'add_daily':
        if($power->student_add_daily == 10){
          return true;
        }else{
          return false;
        };
        break;
      case 'comment_daily':
        if($power->student_comment_daily == 10){
          return true;
        }else{
          return false;
        };
        break;
      default:
        return true;
        break;
    }
  }
  //获取权限
  public function actionGetPower(){
    if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type'))){
      //判断是否为校长
      if(Yii::$app->request->post('user_type') == 'teacher'){
        //非校长不可操作
        $role     = Yii::$app->authManager->getRolesByUser(Yii::$app->request->post('user_id'));
        $role_key =  key($role);

        if(!(substr($role_key,-3) == '001' || substr($role_key,-3) == '007') ) {
            return [
              'success' => false,
              'message' => '非校长无权限操作'
              ];
        }
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        $power = StudioPower::findOne(['studio_id'=>$studio_id]);
        if(empty($power)){
          $addpower = new StudioPower();
          $addpower->studio_id = $studio_id;
          $addpower->save();
          $power = StudioPower::findOne(['studio_id'=>$studio_id])->toArray();
        }else{
          $power = $power->toArray();
        }
        $res = array();
        $i = 1;
        $res[] = array(
            'id' => $i++,
            'title' => '校外学生或家长查看校园动态',
            'value' => $power['people_look_daily']==10?true:false,
            'type' => 'people_look_daily',
            'stateNum' => $power['people_look_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '校外学生或家长发布校园动态',
            'value' => $power['people_add_daily']==10?true:false,
            'type' => 'people_add_daily',
            'stateNum' =>$power['people_add_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '校外学生或家长评论校园动态',
            'value' => $power['people_comment_daily']==10?true:false,
            'type' => 'people_comment_daily',
            'stateNum' =>$power['people_comment_daily']==10?20:10,
            'desc' =>'*校外学生就是没有激活的学生用户',
            'is_space'=>true,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '在校学生发布校园动态',
            'value' => $power['student_add_daily']==10?true:false,
            'type' => 'student_add_daily',
            'stateNum' =>$power['student_add_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '在校学生评论校园动态',
            'value' => $power['student_comment_daily']==10?true:false,
            'type' => 'student_comment_daily',
            'stateNum' =>$power['student_comment_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );

        $arr[] = array('title'=>'官微','powerValue'=>$res);

        return [
            'success' => true,
            'data' => $arr,
            'message' => 'success'
        ];
      }else{
        return [
              'success' => false,
              'message' => '非校长身份不可更改权限'
          ];
      }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }
  //变更权限
  public function actionUpdatePower(){
    if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('power_type')) && !empty(Yii::$app->request->post('power_value'))){
      //判断是否为校长
      if(Yii::$app->request->post('user_type') == 'teacher'){
        //非校长不可操作
        $role     = Yii::$app->authManager->getRolesByUser(Yii::$app->request->post('user_id'));
        $role_key =  key($role);

        if(substr($role_key,-3) != '001') {
            return [
              'success' => false,
              'message' => '非校长不能置顶动态'
              ];
        }
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        $power = StudioPower::findOne(['studio_id'=>$studio_id]);
        $type = Yii::$app->request->post('power_type');
        if(empty($power)){
          $addpower = new StudioPower();
          $addpower->studio_id = $studio_id;
          $addpower->$type = Yii::$app->request->post('power_value');
          $addpower->save();
        }else{
          $power->$type = Yii::$app->request->post('power_value');
          $power->save();
        }
        $power = StudioPower::findOne(['studio_id'=>$studio_id])->toArray();


                $res = array();
        $i = 1;
        $res[] = array(
            'id' => $i++,
            'title' => '校外学生或家长查看校园动态',
            'value' => $power['people_look_daily']==10?true:false,
            'type' => 'people_look_daily',
            'stateNum' => $power['people_look_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '校外学生或家长发布校园动态',
            'value' => $power['people_add_daily']==10?true:false,
            'type' => 'people_add_daily',
            'stateNum' =>$power['people_add_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '校外学生或家长评论校园动态',
            'value' => $power['people_comment_daily']==10?true:false,
            'type' => 'people_comment_daily',
            'stateNum' =>$power['people_comment_daily']==10?20:10,
            'desc' =>'*校外学生就是没有激活的学生用户',
            'is_space'=>true,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '在校学生发布校园动态',
            'value' => $power['student_add_daily']==10?true:false,
            'type' => 'student_add_daily',
            'stateNum' =>$power['student_add_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $res[] = array(
            'id' => $i++,
            'title' => '在校学生评论校园动态',
            'value' => $power['student_comment_daily']==10?true:false,
            'type' => 'student_comment_daily',
            'stateNum' =>$power['student_comment_daily']==10?20:10,
            'desc' =>'',
            'is_space'=>false,
          );
        $arr[] = array('title'=>'官微','powerValue'=>$res);
        return [
              'success' => true,
              'data' => $arr,
              'message' => 'success'
          ];
      }else{
        return [
              'success' => false,
              'message' => '非校长身份不可更改权限'
          ];
      }
    }else{
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }


  //拉黑
  public function actionDelUser(){
    if((!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('del_user_id')) && !empty(Yii::$app->request->post('del_user_type')))||(!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('del_user_id')) && !empty(Yii::$app->request->get('del_user_type'))))  {

      if(Yii::$app->request->post('user_type') == 'teacher'){

        if(Yii::$app->request->post('del_user_type') == 'teacher'){
          $user = Admin::findOne(Yii::$app->request->post('del_user_id'));
          $user->status = 0;
          $user->save();

        }else if(Yii::$app->request->post('del_user_type') == 'student'){
          $user = User::findOne(Yii::$app->request->post('del_user_id'));
          $user->status = 0;
          $user->save();

        }else{
          $user = Family::findOne(Yii::$app->request->post('del_user_id'));
          $user->status = 0;
          $user->save();

        }

        $daily = Daily::findAll(['user_id'=>Yii::$app->request->post('del_user_id'),'user_type'=>Yii::$app->request->post('del_user_type'),'status'=>1]);

        if(!empty($daily)){
          foreach ($daily as $key => $value) {
            $tamp = array();
            $temp = Daily::findOne($value->daily_id);
            $temp->status = 2;
            $temp->save();
          }
        }
        return [
            'success' => true,
            'data' => $news,
            'message' => '删除用户成功'
        ];
      }else{
        return [
              'success' => false,
              'message' => '非老师身份不可删除用户'
          ];
      }
    }
  }


  //获取直播
  public function actionGetLive(){
    $live = Curl::metis_file_get_contents(
                    'https://api.teacher.meishuquanyunxiao.com/v2/cc-live/get-cc-list?user_id='.Yii::$app->request->post('user_id')."&user_type=".Yii::$app->request->post('user_type')."&limit=1&page=0"
                );
      if($live[0]->live_type == "back"){
        $live[0] = NULL;
      }
    return [
            'success' => true,
            'data' => $live[0],
            'message' => '获取数据成功'
        ];
  }

  //获取名字
  public function actionGetName(){
    if((!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) ) || (!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) ) ) {


      if(!empty(Yii::$app->request->post('user_id'))){

        if(Yii::$app->request->post('user_type') == 'teacher'){
          $studio_id= Admin::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }else if(Yii::$app->request->post('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }
        if($studio_id == 0){
          $studio_id = Yii::$app->request->post('studio_id');
        }
      }
      if(!empty(Yii::$app->request->get('user_id'))){

        if(Yii::$app->request->get('user_type') == 'teacher'){
          $studio_id= Admin::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }else if(Yii::$app->request->get('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }
        if($studio_id == 0){
          $studio_id = Yii::$app->request->get('studio_id');
        }
      }


      $studio = Studio::findOne($studio_id);
      $size = Yii::$app->params['oss']['Size']['original'];
      $news = News::findAll(['status'=>10,'studio_type'=>$studio->studio_type]);
      
      foreach ($news as $key => $value) {
        $news[$key]['icon'] = Oss::getIcon($value['icon']).$size;
        //$news[$key]['msg'] = "定制版块--入驻美术世界，或定制云校App可用";

        $news[$key]['isArtWorld'] = $value['isArtWorld']==1?true:false;
        if(!empty(Yii::$app->request->post('type')) && Yii::$app->request->post('type')=="MiniProgram"){
          if($news[$key]['type']=='yxzb' || $news[$key]['type']=='zsjz' || $news[$key]['type']=='yxzp'){
            unset($news[$key]);
          }
        }
      }
      return [
              'success' => true,
              'data' => $news,
              'message' => '获取数据成功'
          ];
      }
  }
  //获取轮播图
  public function actionGetBanner(){
    if((!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) )||(!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')))){
      if(Yii::$app->request->post('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }
      if($studio_id == 0){
        $studio_id = Yii::$app->request->post('studio_id');
      }
      $new_list = NewList::find()->where(['is_banner'=>10,'status'=>10,'studio_id'=>$studio_id])->orderby('updated_at desc')->limit(3)->asArray()->all();
      $prize_list = PrizeList::find()->where(['is_banner'=>10,'status'=>10,'studio_id'=>$studio_id])->orderby('prize_list_id desc')->limit(3)->asArray()->all();
      $enrollment_guide_list = EnrollmentGuideList::find()->where(['is_banner'=>10,'status'=>10,'studio_id'=>$studio_id])->orderby('enrollment_guide_list_id desc')->limit(3)->asArray()->all();
      $prointroduction = Prointroduction::find()->where(['is_banner'=>10,'status'=>10,'studio_id'=>$studio_id])->orderby('prointroduction_id desc')->limit(3)->asArray()->all();

      $res = array();
      for ($i=0; $i < 3; $i++) {
        if(!empty($new_list[$i])){
          $new_list[$i]['thumbnails'] = OSS::getUrl($studio_id,'new','thumbnails',$new_list[$i]['thumbnails']).Yii::$app->params['oss']['Size']['950x540'];
          $new_list[$i]['share']= array(
                              'share_title'=>$new_list[$i]['name'],
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($new_list[$i]['url'])?'https://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id='.$new_list[$i]['new_list_id'].'&is_banner=1':$new_list[$i]['url'],
                              'share_image'=>$new_list[$i]['thumbnails']);
          $new_list[$i]['url'] = empty($new_list[$i]['url'])?'https://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id='.$new_list[$i]['new_list_id']:$new_list[$i]['url'];
          $res[] = $new_list[$i];
        }
        if(!empty($prize_list[$i])){
          $prize_list[$i]['thumbnails'] = OSS::getUrl($studio_id,'prize','thumbnails',$prize_list[$i]['thumbnails']).Yii::$app->params['oss']['Size']['950x540'];
          $prize_list[$i]['share']= array(
                              'share_title'=>$prize_list[$i]['name'],
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($prize_list[$i]['url'])?'https://www.meishuquanyunxiao.com/share/prize-list-view.html?prize_list_id='.$prize_list[$i]['prize_list_id'].'&is_banner=1':"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=prize_list&is_banner=1&id=".$prize_list[$i]['prize_list_id'],
                              'share_image'=>$prize_list[$i]['thumbnails']);
          $prize_list[$i]['url'] = empty($prize_list[$i]['url'])?'https://www.meishuquanyunxiao.com/share/prize-list-view.html?prize_list_id='.$prize_list[$i]['prize_list_id']:"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=prize_list&id=".$prize_list[$i]['prize_list_id'];
          $res[] = $prize_list[$i];
        }
        if(!empty($prointroduction[$i])){
          $prointroduction[$i]['thumbnails'] = OSS::getUrl($studio_id,'prointroduction','thumbnails',$prointroduction[$i]['thumbnails']).Yii::$app->params['oss']['Size']['950x540'];
          $prointroduction[$i]['share']= array(
                              'share_title'=>$prointroduction[$i]['name'],
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($prointroduction[$i]['url'])?'https://www.meishuquanyunxiao.com/share/prointroduction-view.html?prointroduction_id='.$prointroduction[$i]['prointroduction_id'].'&is_banner=1':$prointroduction[$i]['url'],
                              'share_image'=>$prointroduction[$i]['thumbnails']);
          $prointroduction[$i]['url'] = empty($prointroduction[$i]['url'])?'https://www.meishuquanyunxiao.com/share/prointroduction_id-view.html?prointroduction_id='.$prointroduction[$i]['prointroduction_id']:$prointroduction[$i]['url'];
          $res[] = $prointroduction[$i];
        }
        if(!empty($enrollment_guide_list[$i])){
          $enrollment_guide_list[$i]['thumbnails'] = OSS::getUrl($studio_id,'enrollment','thumbnails',$enrollment_guide_list[$i]['thumbnails']).Yii::$app->params['oss']['Size']['950x540'];
          $enrollment_guide_list[$i]['share']= array(
                              'share_title'=>$enrollment_guide_list[$i]['name'],
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($enrollment_guide_list[$i]['url'])?'https://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$enrollment_guide_list[$i]['enrollment_guide_list_id'].'&is_banner=1':"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=enrollment_guide_list&is_banner=1&id=".$enrollment_guide_list[$i]['enrollment_guide_list_id'],
                              'share_image'=>$enrollment_guide_list[$i]['thumbnails']);
          $enrollment_guide_list[$i]['url'] = empty($enrollment_guide_list[$i]['url'])?'https://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$enrollment_guide_list[$i]['enrollment_guide_list_id']:"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=enrollment_guide_list&id=".$enrollment_guide_list[$i]['enrollment_guide_list_id'];
          $res[] = $enrollment_guide_list[$i];
        }
      }
      $msg['status'] = 0;
      $msg['message'] ='正常使用';

      if(!empty(Yii::$app->request->post('code')) ){
        $code = ActivationCode::findOne(['code'=>Yii::$app->request->post('code')]);

        if($code->type == 1) {
          $code->activetime = 1;
        }

        $codetime = strtotime($code->due_time)-time();

        if($codetime/(24*3600) <= 7 && $codetime/(24*3600) > 0){
          $msg['status'] = 1;
          $msg['message'] = "您的激活码还有".ceil($codetime/(24*3600))."天过期";
        }else if($codetime < 0){
          $msg['status'] = 1;
          $msg['message'] = "您的激活码已过期，请联系管理员";
        }
      }

      return [
              'success' => true,
              'data' => $res,
              'codemsg' => $msg,
              'message' => '获取数据成功'
          ];
    }
  }


  //获取轮播图
  public function actionGetBannerV2(){

    $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['metis-video'].'?limit=5&page=0&charging_option=3'
        );
    
    foreach ($courses as $key => $value) {
      if(strstr($value->preview, 'web.meishuquan')) {
          $pathinfo = pathinfo($value->preview);
          $thumbs_image =  $pathinfo['dirname'].'/'.basename($value->preview,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
      }else{
          $thumbs_image = $value->preview.'?x-oss-process=style/fix_width';
      }
      
      $host_info = 'http://web.meishuquan.net/uploads/ueditor/';
      $desc = str_replace('/uploads/ueditor/',"$host_info",$value->desc);

      $course_list[$key]['thumbnails'] = $thumbs_image;
      $course_list[$key]['share']= array(
                          'share_title'=>$course_list[$key]['name'],
                          'share_desc'=>'美术世界分享',
                          'share_url'=>$value->share_link,
                          'share_image'=>$thumbs_image);
      $course_list[$key]['url'] = $value->share_link;
      $course_list[$key]['type'] = "course";
      $course_list[$key]['tid'] = $value->id;
      
      $res[] = $course_list[$key];
    }
    
    $msg['status'] = 0;
    $msg['message'] ='正常使用';

    return [
              'success' => true,
              'data' => $res,
              'codemsg' => $msg,
              'message' => '获取数据成功'
          ];
          
  }
  //获取官微列表
	public function actionGetList()
  {
    if((!empty(Yii::$app->request->post('name')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) ) || (!empty(Yii::$app->request->get('name')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) )) {

      if(!empty(Yii::$app->request->post('name'))){
        $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
        $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
        $offset = $page*$limit;
         if(Yii::$app->request->post('user_type') == 'teacher'){
          $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        }else if(Yii::$app->request->post('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }
        if($studio_id == 0){
          $studio_id = Yii::$app->request->post('studio_id');
        }
        $name = Yii::$app->request->post('name');

      }
      if(!empty(Yii::$app->request->get('name'))){
        $page = empty(Yii::$app->request->get('page'))?0:Yii::$app->request->get('page');
        $limit = empty(Yii::$app->request->get('limit'))?5:Yii::$app->request->get('limit');
        $offset = $page*$limit;
         if(Yii::$app->request->get('user_type') == 'teacher'){
          $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
        }else if(Yii::$app->request->get('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }
        if($studio_id == 0){
          $studio_id = Yii::$app->request->get('studio_id');
        }

        $name = Yii::$app->request->get('name');
      }
      switch ($name)
      {
        case 'NewList':
            return [
                'success' => true,
                'data' => $this->GetNewList($studio_id,$offset,$limit),
                'message' => '获取数据成功'
            ];break;
        case 'ProIntroduction':
            return [
                'success' => true,
                'data' => $this->GetProIntroduction($studio_id,$offset,$limit),
                'message' => '获取数据成功'
            ];break;
    
        case 'PrizeList':
          if($studio_id == 183){
            return [
                'success' => false,
                'data' => NULL,
                'message' => '定制版块——入驻美术世界，或定制云校APP后可用'
            ];break;
          }else{
            return [
                'success' => true,
                'data' => $this->GetPrizeList($studio_id,$offset,$limit),
                'message' => '获取数据成功'
            ];break;
          }

        case 'TeacherList':
          return [
              'success' => true,
              'data' => $this->GetTeacherList($studio_id,$offset,$limit),
              'message' => '获取数据成功'
          ];break;

        case 'WorksList':
          if($studio_id == 183){
            return [
                'success' => false,
                'data' => NULL,
                'message' => '定制版块——入驻美术世界，或定制云校APP后可用'
            ];break;
          }else{
            return [
                'success' => true,
                'data' => $this->GetWorksList($studio_id,$offset,4),
                'message' => '获取数据成功'
            ];break;
          }
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
        case 'YuanXiaoBaoKao':
          $YuanXiaoBaoKao['list'][] = array();
          return [
              'success' => true,
              'data' => $YuanXiaoBaoKao,
              'message' => '获取数据成功'
          ];break;
        case 'SchoolPic':
          if($studio_id == 183){
            return [
                'success' => false,
                'data' => NULL,
                'message' => '定制版块——入驻美术世界，或定制云校APP后可用'
            ];break;
          }else{
            return [
                'success' => true,
                'data' => $this->GetSchoolPic($studio_id,$offset,$limit),
                'message' => '获取数据成功'
            ];break;
          }
        case 'EnrollmentGuideList':
          if($studio_id == 183){
            return [
                'success' => false,
                'data' => NULL,
                'message' => '定制版块——入驻美术世界，或定制云校APP后可用'
            ];break;
          }else{
            return [
                'success' => true,
                'data' => $this->GetEnrollmentGuideList($studio_id,$offset,$limit),
                'message' => '获取数据成功'
            ];break;
          }
      }
    }
  }
  public function GetPrizeList($studio_id,$offset,$limit){
    $prize_list = PrizeList::find()->where(['status'=>10,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('prize_list_id desc')->all();
    $data=array();
    foreach ($prize_list as $key => $value) {
      $admin = Admin::findOne($value->admin_id);


      $host_info = 'http://backend.meishuquanyunxiao.com/assets/upload/image/';
      $desc = str_replace('/assets/upload/image/',"$host_info",$value->desc);
      $data[] = array(
                 'id'=>$value->prize_list_id,
                 'name'=>$value->name,
                 'Author'=>$admin->name,
                 'url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/prize-list-view.html?prize_list_id='.$value->prize_list_id:$value->url,
                 'share'=> array(
                              'share_title'=>$value->name,
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/prize-list-view.html?prize_list_id='.$value->prize_list_id.'&is_banner=1':"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=prize_list&is_banner=1&id=".$value->prize_list_id,
                              'share_image'=>OSS::getUrl($studio_id,'prize','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original']),
                 'Thumbnails'=>OSS::getUrl($studio_id,'prize','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'],
                 'content'=>$desc,
                 'addTime'=>$value->created_at*1000,
                 'UpTime'=>$value->updated_at*1000,);
    }
    return $data;
  }

  //获取宿舍信息或招生简介
  public function actionGetEnList(){
    if(!empty(Yii::$app->request->post('name')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) ){
      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;
       if(Yii::$app->request->post('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }
      if($studio_id == 0){
        $studio_id = Yii::$app->request->post('studio_id');
      }
      switch (Yii::$app->request->post('name'))
      {
      case 'zhaosheng':
          $en_list = EnrollmentGuideList::find()->where(['status'=>10,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('is_top desc,enrollment_guide_list_id desc')->all();
          $data=array();
          foreach ($en_list as $key => $value) {
            $host_info = 'http://backend.meishuquanyunxiao.com/assets/upload/image/';
            $desc = str_replace('/assets/upload/image/',"$host_info",$value->desc);
            $data[] = array(
                       'id'=>$value->enrollment_guide_list_id,
                       'name'=>$value->name,
                       'url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$value->enrollment_guide_list_id:$value->url,
                       'Thumbnails'=>OSS::getUrl($studio_id,'enrollment','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'],
                        'share'=> array(
                              'share_title'=>$value->name,
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$value->enrollment_guide_list_id.'&is_banner=1':"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=enrollment_guide_list&is_banner=1&id=".$value->enrollment_guide_list_id,
                              'share_image'=>OSS::getUrl($studio_id,'enrollment','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original']),
                       'content'=>$desc,
                       'addTime'=>$value->created_at*1000,
                       'UpTime'=>$value->updated_at*1000,);
          }
          return [
              'success' => true,
              'data' => $data,
              'message' => '获取数据成功'
          ];break;
      case 'sushe':
        
        return [
            'success' => true,
            'data' => $this->GetNewList($studio_id,$offset,$limit),
            'message' => '获取数据成功'
        ];break;
      }
    }else{
      if(!empty(Yii::$app->request->get('name')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) ){
      $page = empty(Yii::$app->request->get('page'))?0:Yii::$app->request->get('page');
      $limit = empty(Yii::$app->request->get('limit'))?5:Yii::$app->request->get('limit');
      $offset = $page*$limit;
       if(Yii::$app->request->get('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->get('user_type') == 'student'){
        $studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }else{
        $studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }
      if($studio_id == 0){
        $studio_id = Yii::$app->request->get('studio_id');
      }
      switch (Yii::$app->request->get('name'))
      {
      case 'zhaosheng':
          $en_list = EnrollmentGuideList::find()->where(['status'=>10,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('is_top desc,enrollment_guide_list_id desc')->all();
          $data=array();
          foreach ($en_list as $key => $value) {
            $host_info = 'http://backend.meishuquanyunxiao.com/assets/upload/image/';
            $desc = str_replace('/assets/upload/image/',"$host_info",$value->desc);
            $data[] = array(
                       'id'=>$value->enrollment_guide_list_id,
                       'name'=>$value->name,
                       'url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$value->enrollment_guide_list_id:$value->url,
                       'Thumbnails'=>OSS::getUrl($studio_id,'enrollment','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'],
                        'share'=> array(
                              'share_title'=>$value->name,
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$value->enrollment_guide_list_id.'&is_banner=1':"https://www.meishuquanyunxiao.com/share/web-view.html?studio_id=".$studio_id."&type=enrollment_guide_list&is_banner=1&id=".$value->enrollment_guide_list_id,
                              'share_image'=>OSS::getUrl($studio_id,'enrollment','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original']),
                       'content'=>$desc,
                       'addTime'=>$value->created_at*1000,
                       'UpTime'=>$value->updated_at*1000,);
          }
          return [
              'success' => true,
              'data' => $data,
              'message' => '获取数据成功'
          ];break;
      case 'sushe':
        
        return [
            'success' => true,
            'data' => $this->GetNewList($studio_id,$offset,$limit),
            'message' => '获取数据成功'
        ];break;
      }
    }
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }
  public function GetEnrollmentGuideList($studio_id,$offset,$limit){
      $data['obj1'][0] = array(
                 'id'=>1,
                 'name'=>'招生简章',
                 'type'=>'zhaosheng',
                 'Thumbnails'=>Oss::getIcon('ZhaoshengJianZhang (7).jpg').$size.Yii::$app->params['oss']['Size']['original']);
      // $data[1] = array(
      //            'id'=>2,
      //            'type'=>'sushe',
      //            'name'=>'抢宿舍',
      //            'Thumbnails'=>Oss::getIcon('ZhaoshengJianZhang (7).jpg').$size.Yii::$app->params['oss']['Size']['original']);

      $registration = Campus::find()->where(['studio_id'=>$studio_id,'status'=>10])->all();
      if(!empty($registration)){
        foreach ($registration as $key => $value) {
          if(!empty($registration[$key]->address) || !empty($registration[$key]->pic)){
            $data['obj2'][] = array(
                    'id' => $registration[$key]->id,
                    'name' => $registration[$key]->name,
                    'lat' => $registration[$key]->lng,
                    'lng' => $registration[$key]->lat,
                    'address' => $registration[$key]->address,
                    'pic' => OSS::getUrl($registration[$key]->studio_id,'registration','pic',$registration[$key]->pic).Yii::$app->params['oss']['Size']['original'],
                    'phone_number'  => $registration[$key]->phone_number,
            );
            if(!empty($registration[$key]->phone_number)){
              $data['obj3'] = $registration[$key]->phone_number;
            }
          }
        }
      }else{
        $data['obj2'] = array();
        $data['obj3'] = NULL;
      }
    return $data;
  }
  //报名
  public function actionPostRegistration(){
    if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type'))){

        if(Yii::$app->request->post('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;

          if($studio_id == 0){
            $studio_id = Yii::$app->request->post('studio_id');
          }

          $user = RegistrationUser::find()->where(['user_id'=>Yii::$app->request->post('user_id'),'studio_id'=>$studio_id])->one();
          if(!empty($user)){
            return [
              'success' => false,
              'message' => '您已报过名，请等待学校通知'
            ];
          }else{
            $model  = new RegistrationUser();
            $model->user_id = Yii::$app->request->post('user_id');
            $model->user_type = 'student';
            $model->studio_id = $studio_id;
            $model->timer = time();
            $model->save();
            return [
              'success' => true,
              'message' => '提交成功'
            ];
          }
        }else{
           return [
              'success' => false,
              'message' => '非学生不可以报名'
          ];
        }

    }else{

      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  public function GetNewList($studio_id,$offset,$limit){
    $new_list = NewList::find()->where(['status'=>10,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('updated_at desc')->all();
    $data['list'] = array();
    foreach ($new_list as $key => $value) {
      $data['list'][] = array(
                     'name'=>$value->name,
                     'url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id='.$value->new_list_id:$value->url,
                     'id'=>$value->new_list_id,
                     'thumbnails'=>array(array('url'=>OSS::getUrl($studio_id,'new','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'])),
                      'created_at'=>$value->created_at*1000,
                      'updated_at'=>$value->updated_at*1000,
                      'is_top' => $value->is_top,
                      'share'=> array(
                              'share_title'=>$value->name,
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id='.$value->new_list_id.'&is_banner=1':$value->url,
                              'share_image'=>OSS::getUrl($studio_id,'new','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'],
                            )
                      );
    }
    return $data;
  }
  public function GetProIntroduction($studio_id,$offset,$limit){
    $Prointroduction = Prointroduction::find()->where(['status'=>10,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('updated_at desc')->all();
    $data['list'] = array();
    foreach ($Prointroduction as $key => $value) {
      $data['list'][] = array(
                     'name'=>$value->name,
                     'url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/prointroduction-view.html?prointroduction_id='.$value->prointroduction_id:$value->url,
                     'id'=>$value->prointroduction_id,
                     'thumbnails'=>array(array('url'=>OSS::getUrl($studio_id,'prointroduction','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'])),
                      'created_at'=>$value->created_at*1000,
                      'updated_at'=>$value->updated_at*1000,
                      'is_top' => $value->is_top,
                      'share'=> array(
                              'share_title'=>$value->name,
                              'share_desc'=>'云校分享',
                              'share_url'=>empty($value->url)?'https://www.meishuquanyunxiao.com/share/prointroduction-view.html?prointroduction_id='.$value->prointroduction_id.'&is_banner=1':$value->url,
                              'share_image'=>OSS::getUrl($studio_id,'prointroduction','thumbnails',$value->thumbnails).Yii::$app->params['oss']['Size']['original'],
                            )
                      );
    }
    return $data;
  }

  public function GetTeacherList($studio_id,$offset,$limit){
    $teacher_list = TeacherList::find()->where(['status'=>10,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('teacher_list_id ASC')->all();
    $data=array();
    foreach ($teacher_list as $key => $value) {
        $data[] = array(
                           'name'=>$value->name,
                           'content'=>$value->desc,
                           'url'=>'https://www.meishuquanyunxiao.com/share/teacher-list-view.html?teacher_list_id='.$value->teacher_list_id,
                           'auth' => $value->auth,
                           'id'=>$value->teacher_list_id,
                           'pic_url'=>OSS::getUrl($studio_id,'teacher','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
                           'share'=> array(
                              'share_title'=>$value->name,
                              'share_desc'=>'云校分享',
                              'share_url'=>'https://www.meishuquanyunxiao.com/share/teacher-list-view.html?teacher_list_id='.$value->teacher_list_id.'&is_banner=1',
                              'share_image'=>OSS::getUrl($studio_id,'teacher','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
                            )
                       );
    }
    return $data;
  }
  public function GetWorksList($studio_id,$offset,$limit){
    $student_works = WorksList::find()->where(['status'=>10,'studio_id'=>$studio_id,'is_teacher'=>0])->limit($limit)->orderby('works_list_id desc')->all();
    $student_arr['data'] = array();
    foreach ($student_works as $key => $value){
      $student_arr['data'][] = array('name'=>$value->name,
                          'id'=>$value->works_list_id,
                          'pic_url'=>OSS::getUrl($studio_id,'works','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
                          'url'=>'www.baidu.com',
                  );
    }
    $studio = Studio::findOne($studio_id);
    if($studio->type==1){
      $student_arr['name'] = '学生作品';
    }else{
      $student_arr['name'] = '艺术院校';
    }
    
    $student_arr['auth'] = 'student';
    $student_arr['url'] = 'www.baidu.com';
    $data[] = $student_arr;

    $teacher_works = WorksList::find()->where(['status'=>10,'studio_id'=>$studio_id,'is_teacher'=>10])->limit($limit)->orderby('works_list_id desc')->all();
    $teacher_arr['data'] = array();
    foreach ($teacher_works as $key => $value) {
      $teacher_arr['data'][] = array('name'=>$value->name,
                          'content'=>$value->desc,
                          'id'=>$value->works_list_id,
                          'pic_url'=>OSS::getUrl($studio_id,'works','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],'url'=>'www.baidu.com',
                  );
    }
    if($studio->type==1){
      $teacher_arr['name'] = '老师作品';
    }else{
      $teacher_arr['name'] = '综合类大学';
    }
    $teacher_arr['auth'] = 'teacher';
    $teacher_arr['url'] = 'www.baidu.com';
    $data[] = $teacher_arr;
    
    return $data;
  }

  //获取作业分类
    public function actionGetWorksType(){
      if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('auth'))){
        $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
        $limit = empty(Yii::$app->request->post('limit'))?12:Yii::$app->request->post('limit');
        $offset = $page*$limit;
        $auth = Yii::$app->request->post('auth')=='teacher'?10:0;

         if(Yii::$app->request->post('user_type') == 'teacher'){
          $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        }else if(Yii::$app->request->post('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }

        if($studio_id == 0){
            $studio_id = Yii::$app->request->post('studio_id');
          }

        if(!empty(Yii::$app->request->post('type'))){
          $where['type'] = Yii::$app->request->post('type');
        }
        $where['status'] = 10;
        $where['studio_id'] = $studio_id;
        $where['is_teacher'] = $auth;
        $works_list = WorksList::find()->where($where)->offset($offset)->limit($limit)->orderby('works_list_id desc')->all();
        $res = array();
        foreach ($works_list as $key => $value) {
          $res[$key] = array('name'=>$value->name,
                    'content'=>$value->desc,
                    'id'=>$value->works_list_id,
                    'pic_url'=>OSS::getUrl($studio_id,'works','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
            );
        }

        $data['type'] = array(array('id'=>0,'name'=>'全部'),
                              array('id'=>10,'name'=>'素描'),
                            array('id'=>20,'name'=>'色彩'),
                          array('id'=>30,'name'=>'设计'),
                          array('id'=>40,'name'=>'速写'));
        $data['list'] = $res;
        return [
              'success' => true,
              'data' => $data,
              'message' => '获取数据成功',
          ];
      }else{
        return [
          'success' => false,
          'message' => '非POST传值或缺少参数'
        ];
      }
    }
  //获取作业分类
    public function actionPostWorksList(){
      if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('auth'))){
        $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
        $limit = empty(Yii::$app->request->post('limit'))?12:Yii::$app->request->post('limit');
        $offset = $page*$limit;
        $auth = Yii::$app->request->post('auth')=='teacher'?10:0;

         if(Yii::$app->request->post('user_type') == 'teacher'){
            $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
          }else if(Yii::$app->request->post('user_type') == 'student'){
            $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
          }else{
            $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
          }
          if($studio_id == 0){
            $studio_id = Yii::$app->request->post('studio_id');
          }


        if(!empty(Yii::$app->request->post('type'))){
          $where['type'] = Yii::$app->request->post('type');
        }
        $where['status'] = 10;
        $where['studio_id'] = $studio_id;
        $where['is_teacher'] = $auth;
        $works_list = WorksList::find()->where($where)->offset($offset)->limit($limit)->orderby('works_list_id desc')->all();
        $res = array();
        foreach ($works_list as $key => $value) {
          $res[$key] = array('name'=>$value->name,
                    'content'=>$value->desc,
                    'id'=>$value->works_list_id,
                    'pic_url'=>OSS::getUrl($studio_id,'works','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
            );
        }
        return [
              'success' => true,
              'data' => $res,
              'message' => '获取数据成功',
          ];
      }else{
        return [
          'success' => false,
          'message' => '非POST传值或缺少参数'
        ];
      }
    }
    public function GetSchoolPic($studio_id,$offset,$limit){
      $school_pic = SchoolPic::find()->where(['studio_id'=>$studio_id,'status'=>10])->offset($offset)->limit($limit)->orderby('school_pic_id ASC')->all();
        $res = array();
        foreach ($school_pic as $key => $value) {
          $res[$key] = array('content'=>$value->desc,
                    'id'=>$value->school_pic_id,
                    'pic_url'=>OSS::getUrl($studio_id,'school','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
            );
        }
        $data['type'] = array(array('id'=>10,'name'=>'校园'),
                            array('id'=>20,'name'=>'教室'),
                          array('id'=>30,'name'=>'食堂'),
                          array('id'=>40,'name'=>'宿舍'),array('id'=>60,'name'=>'超市'),array('id'=>50,'name'=>'其他'));
        $data['list'] = $res;
        return $data;
    }
    //按分类获取校园环境

    public function actionPostSchoolPic(){
      if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type'))){
        //$page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
        //$limit = empty(Yii::$app->request->post('limit'))?12:Yii::$app->request->post('limit');

        $page = 0;
        $limit = 100;
        $offset = $page*$limit;

         if(Yii::$app->request->post('user_type') == 'teacher'){
          $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        }else if(Yii::$app->request->post('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }

        if($studio_id == 0){
            $studio_id = Yii::$app->request->post('studio_id');
          }

        if(!empty(Yii::$app->request->post('type'))){
          $where['type'] = Yii::$app->request->post('type');
        }
        $where['status'] = 10;
        $where['studio_id'] = $studio_id;
        $school_pic = SchoolPic::find()->where($where)->offset($offset)->limit($limit)->orderby('school_pic_id ASC')->all();
        $res = array();
        foreach ($school_pic as $key => $value) {
          $res[$key] = array('content'=>$value->desc,
                    'id'=>$value->school_pic_id,
                    'pic_url'=>OSS::getUrl($studio_id,'school','pic_url',$value->pic_url).Yii::$app->params['oss']['Size']['original'],
            );
        }
        return [
              'success' => true,
              'data' => $res,
              'message' => '获取数据成功',
          ];
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
      $daily->comment_time = time();
      $daily->timer = time();
       if(Yii::$app->request->post('user_type') == 'teacher'){
        $daily->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $daily->studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $daily->studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }
      if($daily->studio_id == 0){
            $daily->studio_id = Yii::$app->request->post('studio_id');
          }
      if(!$this->is_power(Yii::$app->request->post('user_id'),Yii::$app->request->post('user_type'),'add_daily',$daily->studio_id)){
        return [
            'success' => false,
            'message' => '你没有权限发布校园动态'
        ];
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
          if( !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('content'))){
      $daily = new Daily();
      $daily->user_type = Yii::$app->request->get('user_type');
      $daily->user_id = Yii::$app->request->get('user_id');
      $daily->content = Yii::$app->request->get('content');
      $image_url_came = "";
      if(!empty(Yii::$app->request->get('image_url_came'))){
        $img = Yii::$app->request->get('image_url_came');
        foreach ($img as $key => $value) {
          $image_url_came .= ",".$value;
        }
        $image_url_came = substr($image_url_came,1,strlen($image_url_came));
        $daily->image_url_came = $image_url_came;
      }
      $daily->comment_time = time();
      $daily->timer = time();
       if(Yii::$app->request->get('user_type') == 'teacher'){
        $daily->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->get('user_type') == 'student'){
        $daily->studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }else{
        $daily->studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }
      if($daily->studio_id == 0){
            $daily->studio_id = Yii::$app->request->get('studio_id');
          }
      if(!$this->is_power(Yii::$app->request->get('user_id'),Yii::$app->request->get('user_type'),'add_daily',$daily->studio_id)){
        return [
            'success' => false,
            'message' => '你没有权限发布校园动态'
        ];
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
    }
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //置顶动态
  public function actionSetDailyTop(){
    if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('daily_id'))){
      if(Yii::$app->request->post('user_type') == 'teacher'){
        //非校长不可操作
        $role     = Yii::$app->authManager->getRolesByUser(Yii::$app->request->post('user_id'));
        $role_key =  key($role);

        if(substr($role_key,-3) != '001') {
            return [
              'success' => false,
              'message' => '非校长不能置顶动态'
              ];
        }
        $dailytopset = Daily::find()->where(['status'=>1,'daily_id'=>Yii::$app->request->post('daily_id')])->one();
        if($dailytopset->top_time!=0){
          $dailytopset->top_time=0;
          $dailytopset->save();
          return [
              'success' => true,
              'message' => '取消置顶成功'
          ];
        }

        //查出早先置顶动态删除置顶
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        $daily_top = Daily::find()->where('status = 1 AND top_time != 0 AND studio_id = '.$studio_id)->all();
        if(!empty($daily_top)){
          foreach ($daily_top as $key => $value) {
            $dailytemp = Daily::find()->where(['status'=>1,'daily_id'=>$value->daily_id])->one();
            $dailytemp->top_time = 0;
            $dailytemp->save();
          }
        }
        //动态置顶
        $daily = Daily::find()->where(['status'=>1,'daily_id'=>Yii::$app->request->post('daily_id')])->one();
        $daily->top_time = time();
        $daily->save();
        return [
              'success' => true,
              'message' => '置顶成功'
          ];
      }else{
        return [
          'success' => false,
          'message' => '非校长不能置顶动态'
          ];
      }
    }else{
          if(!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('daily_id'))){
      if(Yii::$app->request->get('user_type') == 'teacher'){
        //非校长不可操作
        $role     = Yii::$app->authManager->getRolesByUser(Yii::$app->request->get('user_id'));
        $role_key =  key($role);

        if(substr($role_key,-3) != '001') {
            return [
              'success' => false,
              'message' => '非校长不能置顶动态'
              ];
        }
        $dailytopset = Daily::find()->where(['status'=>1,'daily_id'=>Yii::$app->request->get('daily_id')])->one();
        if($dailytopset->top_time!=0){
          $dailytopset->top_time=0;
          $dailytopset->save();
          return [
              'success' => true,
              'message' => '取消置顶成功'
          ];
        }

        //查出早先置顶动态删除置顶
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
        $daily_top = Daily::find()->where('status = 1 AND top_time != 0 AND studio_id = '.$studio_id)->all();
        if(!empty($daily_top)){
          foreach ($daily_top as $key => $value) {
            $dailytemp = Daily::find()->where(['status'=>1,'daily_id'=>$value->daily_id])->one();
            $dailytemp->top_time = 0;
            $dailytemp->save();
          }
        }
        //动态置顶
        $daily = Daily::find()->where(['status'=>1,'daily_id'=>Yii::$app->request->get('daily_id')])->one();
        $daily->top_time = time();
        $daily->save();
        return [
              'success' => true,
              'message' => '置顶成功'
          ];
      }else{
        return [
          'success' => false,
          'message' => '非校长不能置顶动态'
          ];
      }
    }
      return [
        'success' => false,
        'message' => '非POST传值或缺少参数'
        ];
    }
  }


  //获取动态
  public function actionGetDaily(){
    if((!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type')) )|| !empty(Yii::$app->request->post('studio_id')) ){
      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;

      $studio_id = 0;


      if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type'))){
        if(Yii::$app->request->post('user_type') == 'teacher'){
          $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
        }else if(Yii::$app->request->post('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
        }

        if(!$this->is_power(Yii::$app->request->post('user_id'),Yii::$app->request->post('user_type'),'look_daily',$studio_id)){
          return [
              'success' => false,
              'message' => '你没有权限查看校园动态'
          ];
        }

      }
      

      if($studio_id == 0){
        $studio_id = Yii::$app->request->post('studio_id');
      }



      $daily = Daily::find()->where(['status'=>1,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('top_time desc,comment_time desc')->all();
      $res = array();
      $is_del = false;
      foreach ($daily as $key => $value) {
        $image_url_came = array();
        $image_url_came_thumb = array();


        if(!empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('user_type'))){
          if(Yii::$app->request->post('user_id') == $value->user_id && Yii::$app->request->post('user_type') == $value->user_type){
            $is_del = true;
          }
          $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'follow_user_id'=>$value->user_id,'follow_user_type'=>$value->user_type,'status'=>1]);
          $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1]);
          //非校长不可操作
          $role     = Yii::$app->authManager->getRolesByUser(Yii::$app->request->post('user_id'));
          $role_key =  key($role);

          if(substr($role_key,-3) == '001'){
            $is_del = true;
          }
        }

 


        if($value->user_type == 'teacher'){
          $user = Admin::findOne($value->user_id);
          $is_parent = 0;
        }else if($value->user_type  == 'student'){
          $user = User::findOne($value->user_id);
          $is_parent = 0;
        }else{
          $user = Family::findOne($value->user_id);
          $is_parent = 1;
        }
        if(!empty($value->image_url_came)){
          $arr = explode(',',$value->image_url_came);
          foreach ($arr as $k => $v) {
            $temp = explode("?", $v);
            $image_url_came[$k]['url'] = $temp[0].'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c';
            $image_url_came_thumb[$k]['url'] = $temp[0].'?x-oss-process=style/250x250';
          }
        }
        //关注相关数据




        $follow_num = UserFollow::find()->where(['follow_user_id'=>$value->user_id,'follow_user_type'=>$value->user_type,'status'=>1])->count();

        //点赞相关数据
        
        $like_num = UserLike::find()->where(['like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1])->count();

        //是否激活
        $is_activating = ActivationCode::findOne(['relation_id'=>$value->user_id,'type'=>($value->user_type == 'teacher'?1:($value->user_type  == 'student'?2:0)),'is_active'=>10,'status'=>10]);
        //数据整理
        $res[] = array(
            'id'=> $value->daily_id, 
            'timer' => $value->timer*1000,
            'avatar' => empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).'?x-oss-process=style/57x57',
            'name' => $user->name,
            'follow_user_id' => $value->user_id,
            'addres' => '',
            'status' => $value->user_type == 'teacher'?'老师':($value->user_type == 'family'?'家长':'学生'),
            'follow_user_type' => $value->user_type,
            'is_follow' => !empty($is_follow)?true:false,//是否关注
            'follow' => (int)$follow_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'is_parent' => !empty($is_parent)?true:false,
            'is_activating' => !empty($is_activating)?true:false,//教师是否激活
            'content'=> $value->content,//文字
            'image_url_came' => $image_url_came,//图片
            'image_url_came_thumb' => $image_url_came_thumb,
            'is_top' => $value->top_time==0?false:true,
            'views' => $value->views,//查看数
            'is_del' => $is_del,
            'url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->daily_id,
            'comment'=> (int)DailyComment::find()->where(['status'=>1,'daily_id'=>$value->daily_id])->count(1),//评论数
            'like' => (int)$like_num,//点赞
            'share'=> array(
              'share_title'=> $value->content,
              'share_desc'=> $value->content,
              'share_url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->daily_id.'&is_banner=1',
              'share_image'=> empty($image_url_came)?(empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original']):$image_url_came[0]['url'],
            )
        );
        if($key == 2){
          $arrtemp = array(0=>$this->GetNewList($studio_id,$page,1));
          if(!empty($arrtemp[0]['list'])){
            $arrtemp[0]['id'] = rand(111111,999999)*100000000000;
            $res[] = $arrtemp[0];
          }
        }
      }
      return [
                'success' => true,
                'data' => $res,
                'message' => '获取数据成功'
            ];
    }else{
      if((!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type')) )|| !empty(Yii::$app->request->get('studio_id')) ){
      $page = empty(Yii::$app->request->get('page'))?0:Yii::$app->request->get('page');
      $limit = empty(Yii::$app->request->get('limit'))?5:Yii::$app->request->get('limit');
      $offset = $page*$limit;

      $studio_id = 0;


      if(!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type'))){
        if(Yii::$app->request->get('user_type') == 'teacher'){
          $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
        }else if(Yii::$app->request->get('user_type') == 'student'){
          $studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }else{
          $studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
        }

        if(!$this->is_power(Yii::$app->request->get('user_id'),Yii::$app->request->get('user_type'),'look_daily',$studio_id)){
          return [
              'success' => false,
              'message' => '你没有权限查看校园动态'
          ];
        }

      }
      

      if($studio_id == 0){
        $studio_id = Yii::$app->request->get('studio_id');
      }



      $daily = Daily::find()->where(['status'=>1,'studio_id'=>$studio_id])->offset($offset)->limit($limit)->orderby('top_time desc,comment_time desc')->all();
      $res = array();
      $is_del = false;
      foreach ($daily as $key => $value) {
        $image_url_came = array();
        $image_url_came_thumb = array();


        if(!empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('user_type'))){
          if(Yii::$app->request->get('user_id') == $value->user_id && Yii::$app->request->get('user_type') == $value->user_type){
            $is_del = true;
          }
          $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'follow_user_id'=>$value->user_id,'follow_user_type'=>$value->user_type,'status'=>1]);
          $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1]);
          //非校长不可操作
          $role     = Yii::$app->authManager->getRolesByUser(Yii::$app->request->get('user_id'));
          $role_key =  key($role);

          if(substr($role_key,-3) == '001'){
            $is_del = true;
          }
        }

 


        if($value->user_type == 'teacher'){
          $user = Admin::findOne($value->user_id);
          $is_parent = 0;
        }else if($value->user_type  == 'student'){
          $user = User::findOne($value->user_id);
          $is_parent = 0;
        }else{
          $user = Family::findOne($value->user_id);
          $is_parent = 1;
        }
        if(!empty($value->image_url_came)){
          $arr = explode(',',$value->image_url_came);
          foreach ($arr as $k => $v) {
            $temp = explode("?", $v);
            $image_url_came[$k]['url'] = $temp[0].'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c';
            $image_url_came_thumb[$k]['url'] = $temp[0].'?x-oss-process=style/250x250';
          }
        }
        //关注相关数据




        $follow_num = UserFollow::find()->where(['follow_user_id'=>$value->user_id,'follow_user_type'=>$value->user_type,'status'=>1])->count();

        //点赞相关数据
        
        $like_num = UserLike::find()->where(['like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1])->count();

        //是否激活
        $is_activating = ActivationCode::findOne(['relation_id'=>$value->user_id,'type'=>($value->user_type == 'teacher'?1:($value->user_type  == 'student'?2:0)),'is_active'=>10,'status'=>10]);
        //数据整理
        $res[] = array(
            'id'=> $value->daily_id, 
            'timer' => $value->timer*1000,
            'avatar' => empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).'?x-oss-process=style/57x57',
            'name' => $user->name,
            'follow_user_id' => $value->user_id,
            'addres' => '',
            'status' => $value->user_type == 'teacher'?'老师':($value->user_type == 'family'?'家长':'学生'),
            'follow_user_type' => $value->user_type,
            'is_follow' => !empty($is_follow)?true:false,//是否关注
            'follow' => (int)$follow_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'is_parent' => !empty($is_parent)?true:false,
            'is_activating' => !empty($is_activating)?true:false,//教师是否激活
            'content'=> $value->content,//文字
            'image_url_came' => $image_url_came,//图片
            'image_url_came_thumb' => $image_url_came_thumb,
            'is_top' => $value->top_time==0?false:true,
            'views' => $value->views,//查看数
            'is_del' => $is_del,
            'url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->daily_id,
            'comment'=> (int)DailyComment::find()->where(['status'=>1,'daily_id'=>$value->daily_id])->count(1),//评论数
            'like' => (int)$like_num,//点赞
            'share'=> array(
              'share_title'=> $value->content,
              'share_desc'=> $value->content,
              'share_url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->daily_id.'&is_banner=1',
              'share_image'=> empty($image_url_came)?(empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original']):$image_url_came[0]['url'],
            )
        );
        if($key == 2){
          $arrtemp = array(0=>$this->GetNewList($studio_id,$page,1));
          if(!empty($arrtemp[0]['list'])){
            $arrtemp[0]['id'] = rand(111111,999999)*100000000000;
            $res[] = $arrtemp[0];
          }
        }
      }
      return [
                'success' => true,
                'data' => $res,
                'message' => '获取数据成功'
            ];
    }
      return [
        'success' => false,
        'message' => '非POST传值或缺少参数'
        ];
    }
    
  }

  //删除动态
  public function actionDelDaily(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('daily_id'))){
      $daily = Daily::findOne(Yii::$app->request->post('daily_id'));
      if(Yii::$app->request->post('user_type') == 'teacher'){
        $user = Admin::findOne(Yii::$app->request->post('user_id'));
      }else if(Yii::$app->request->post('user_type')  == 'student'){
        $user = User::findOne(Yii::$app->request->post('user_id'));
      }else{
        $user = Family::findOne(Yii::$app->request->post('user_id'));
      }
      if(Yii::$app->request->post('user_type')=='teacher'){
        $daily->status = 2;
        $daily->save();
        return [
                'success' => true,
                'message' => '删除成功'
            ];
      }else if($user->id == $daily->user_id && Yii::$app->request->post('user_type') == $daily->user_type){
        $daily->status = 2;
        $daily->save();
        return [
                'success' => true,
                'message' => '删除成功'
            ];
        
      }else{
        return [
              'success' => false,
              'message' => '您不是老师或发帖本人,删除动态失败'
          ];
      }
    }else{
          if( !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('daily_id'))){
      $daily = Daily::findOne(Yii::$app->request->get('daily_id'));
      if(Yii::$app->request->get('user_type') == 'teacher'){
        $user = Admin::findOne(Yii::$app->request->get('user_id'));
      }else if(Yii::$app->request->get('user_type')  == 'student'){
        $user = User::findOne(Yii::$app->request->get('user_id'));
      }else{
        $user = Family::findOne(Yii::$app->request->get('user_id'));
      }
      if(Yii::$app->request->get('user_type')=='teacher'){
        $daily->status = 2;
        $daily->save();
        return [
                'success' => true,
                'message' => '删除成功'
            ];
      }else if($user->id == $daily->user_id && Yii::$app->request->get('user_type') == $daily->user_type){
        $daily->status = 2;
        $daily->save();
        return [
                'success' => true,
                'message' => '删除成功'
            ];
        
      }else{
        return [
              'success' => false,
              'message' => '您不是老师或发帖本人,删除动态失败'
          ];
      }
    }
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
        if(Yii::$app->request->post('user_type') == 'teacher'){
        $user_like->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $user_like->studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $user_like->studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }

      if($user_like->studio_id == 0){
            $user_like->studio_id = Yii::$app->request->post('studio_id');
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
          if( !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('like_type')) && !empty(Yii::$app->request->get('id'))){
      $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'like_type'=>Yii::$app->request->get('like_type'),'like_id'=>Yii::$app->request->get('id')]);
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
        $user_like->user_type = Yii::$app->request->get('user_type');
        $user_like->user_id = Yii::$app->request->get('user_id');
        $user_like->like_type = Yii::$app->request->get('like_type');
        $user_like->like_id = Yii::$app->request->get('id');

        $user_like->timer = time();
        if(Yii::$app->request->get('user_type') == 'teacher'){
        $user_like->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->get('user_type') == 'student'){
        $user_like->studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }else{
        $user_like->studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }

      if($user_like->studio_id == 0){
            $user_like->studio_id = Yii::$app->request->get('studio_id');
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
    }
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //发表评论
  public function actionAddDailyComment(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('content')) && !empty(Yii::$app->request->post('id'))){

      $daily = Daily::findOne(Yii::$app->request->post('id'));
      $daily->comment_time = time();
      $daily->save();


      $daily_comment = new DailyComment();
      $daily_comment->user_type = Yii::$app->request->post('user_type');
      $daily_comment->user_id = Yii::$app->request->post('user_id');
      $daily_comment->content = Yii::$app->request->post('content');
      $daily_comment->daily_id = Yii::$app->request->post('id');
      $daily_comment->reply_user_id = !empty(Yii::$app->request->post('reply_user_id'))?Yii::$app->request->post('reply_user_id'):0;
      $daily_comment->reply_user_type = !empty(Yii::$app->request->post('reply_user_type'))?Yii::$app->request->post('reply_user_type'):'';

      if(!empty(Yii::$app->request->post('daily_comment_pid'))){
        $daily_comment->daily_comment_pid = Yii::$app->request->post('daily_comment_pid');
      }
      $daily_comment->timer = time();
       if(Yii::$app->request->post('user_type') == 'teacher'){
        $daily_comment->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $daily_comment->studio_id=User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $daily_comment->studio_id= Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }

      if($daily_comment->studio_id == 0){
            $daily_comment->studio_id = Yii::$app->request->post('studio_id');
          }

      if(!$this->is_power(Yii::$app->request->post('user_id'),Yii::$app->request->post('user_type'),'comment_daily',$daily_comment->studio_id)){
        return [
            'success' => false,
            'message' => '你没有权限发布校园动态'
        ];
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
          if( !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('content')) && !empty(Yii::$app->request->get('id'))){

      $daily = Daily::findOne(Yii::$app->request->get('id'));
      $daily->comment_time = time();
      $daily->save();


      $daily_comment = new DailyComment();
      $daily_comment->user_type = Yii::$app->request->get('user_type');
      $daily_comment->user_id = Yii::$app->request->get('user_id');
      $daily_comment->content = Yii::$app->request->get('content');
      $daily_comment->daily_id = Yii::$app->request->get('id');
      $daily_comment->reply_user_id = !empty(Yii::$app->request->get('reply_user_id'))?Yii::$app->request->get('reply_user_id'):0;
      $daily_comment->reply_user_type = !empty(Yii::$app->request->get('reply_user_type'))?Yii::$app->request->get('reply_user_type'):'';

      if(!empty(Yii::$app->request->get('daily_comment_pid'))){
        $daily_comment->daily_comment_pid = Yii::$app->request->get('daily_comment_pid');
      }
      $daily_comment->timer = time();
       if(Yii::$app->request->get('user_type') == 'teacher'){
        $daily_comment->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->get('user_type') == 'student'){
        $daily_comment->studio_id=User::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }else{
        $daily_comment->studio_id= Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }

      if($daily_comment->studio_id == 0){
            $daily_comment->studio_id = Yii::$app->request->get('studio_id');
          }

      if(!$this->is_power(Yii::$app->request->get('user_id'),Yii::$app->request->get('user_type'),'comment_daily',$daily_comment->studio_id)){
        return [
            'success' => false,
            'message' => '你没有权限发布校园动态'
        ];
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
    }
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //获取评论
  public function actionGetDailyComment(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('id'))){

      if(Yii::$app->request->post('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $studio_id=User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $studio_id=Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }

      if($studio_id == 0){
            $studio_id = Yii::$app->request->post('studio_id');
          }
      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;

      $comment = DailyComment::find()->where(['status'=>1,'studio_id'=>$studio_id,'daily_id'=>Yii::$app->request->post('id'),'daily_comment_pid'=>0])->offset($offset)->limit($limit)->orderby('(case when user_id='.Yii::$app->request->post('user_id').' then 1 else 0 end) DESC, daily_comment_id ASC')->all();
      
      $res['comment_list'] = array();
      //动态信息
      $daily = Daily::findOne(Yii::$app->request->post('id'));

      $image_url_came = array();


      $is_parent = 0;
      if($daily->user_type == 'teacher'){
        $user = Admin::findOne($daily->user_id);
      }else if($daily->user_type  == 'student'){
        $user = User::findOne($daily->user_id);
      }else{
        $is_parent = 1;
        $user = Family::findOne($daily->user_id);
      }

      if(!empty($daily->image_url_came)){
        $arr = explode(',',$daily->image_url_came);
        foreach ($arr as $k => $v) {
          $temp = explode("?", $v);
            $image_url_came[$k]['url'] = $temp[0].'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c';
            $image_url_came_thumb[$k]['url'] = $temp[0].'?x-oss-process=style/thumb';
        }
      }
      
      //关注相关数据
      $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'follow_user_id'=>$daily->user_id,'follow_user_type'=>$daily->user_type,'status'=>1]);
      $follow_num = UserFollow::find()->where(['follow_user_id'=>$daily->user_id,'follow_user_type'=>$daily->user_type,'status'=>1])->count();

      //点赞相关数据
      $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>'daily','like_id'=>$daily->daily_id,'status'=>1]);
      $like_num = UserLike::find()->where(['like_type'=>'daily','like_id'=>$daily->daily_id,'status'=>1])->count();

      $like_user = UserLike::find()->where(['like_type'=>'daily','like_id'=>$daily->daily_id,'status'=>1])->orderby('timer desc')->limit(10)->all();
      
      $res['like_user'] = array();
      foreach ($like_user as $key => $value) {
        $like_user_info = array();

        if($value->user_type == 'teacher'){
          $like_user_info['id'] = $value->user_id;
          $like_user_info['user_type'] = 'teacher';
          $like_user_info['name'] = Admin::findOne($value->user_id)->name;
          $like_user_info['avatar'] = empty(Admin::findOne($value->user_id)->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',Admin::findOne($value->user_id)->image).'?x-oss-process=style/57x57';
        }else if($value->user_type  == 'student'){
          $like_user_info['id'] = $value->user_id;
          $like_user_info['user_type'] = 'student';
          $like_user_info['name'] = User::findOne($value->user_id)->name;
          $like_user_info['avatar'] = empty(User::findOne($value->user_id)->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',User::findOne($value->user_id)->image).'?x-oss-process=style/57x57';
        }else{
          $like_user_info['id'] = $value->user_id;
          $like_user_info['user_type'] = 'family';
          $like_user_info['name'] = Family::findOne($value->user_id)->name;
          $like_user_info['avatar'] = empty(Family::findOne($value->user_id)->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',Admin::findOne($value->user_id)->image).'?x-oss-process=style/57x57';
        }

        $res['like_user'][$key] = $like_user_info;
      }
      //是否激活
      $is_activating = ActivationCode::findOne(['relation_id'=>$daily->user_id,'type'=>($daily->user_type == 'teacher'?1:($daily->user_type  == 'student'?2:0)),'is_active'=>10,'status'=>10]);
      $res['daily'] = array(
            'id'=> $daily->daily_id, 
            'timer' => $daily->timer*1000,
            'avatar' => empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).'?x-oss-process=style/57x57',
            'name' => $user->name,
            'follow_user_id' => $daily->user_id,
            'addres' => '',
            'status' => $daily->user_type == 'teacher'?'老师':($daily->user_type == 'family'?'家长':'学生'),
            'follow_user_type' => $daily->user_type,
            'is_follow' => !empty($is_follow)?true:false,//是否关注
            'follow' => (int)$follow_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'is_activating' => !empty($is_activating)?true:false,//教师是否激活
            'is_parent' => !empty($is_parent)?true:false,
            'is_top' => $daily->top_time==0?false:true,
            'content'=> $daily->content,//文字
            'image_url_came' => $image_url_came,//图片
            'views' => $daily->views,//查看数
            'image_url_came_thumb' => $image_url_came_thumb,//图片
            'url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->like_id,
            'comment'=> (int)DailyComment::find()->where(['status'=>1,'daily_id'=>$daily->daily_id])->count(1),//评论数
            'like' => (int)$like_num,//点赞
            'share'=> array(
              'share_title'=>'云校分享',
              'share_desc'=>$daily->content,
              'share_url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->like_id.'&is_banner=1',
              'share_image'=>empty($image_url_came)?(empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original']):$image_url_came[0]['url'],
            )
        );
        $res['new_list'] = $this->GetNewList($studio_id,0,4);

      if(!empty($comment)){
        foreach ($comment as $key => $value) {
          if($value->user_type == 'teacher'){
            $user = Admin::findOne($value->user_id);
          }else if($value->user_type  == 'student'){
            $user = User::findOne($value->user_id);
          }else{
            $user = Family::findOne($value->user_id);
          }
          $rescomment = $this->getcomment($studio_id,$daily->daily_id,$value->daily_comment_id,Yii::$app->request->post('user_id'),Yii::$app->request->post('user_type'));

          $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1]);
          $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1])->count();
          $res['comment_list'][$key] =  array(
            'id' => $value->daily_comment_id,
            'user_id' => $user->id,
            'user_type' => $value->user_type,
            'like_num' => (int)$like_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'name' => $user->name,
            'avatar' =>empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original'],
            'timer' => $value->timer*1000,
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
       if( !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('id'))){

      if(Yii::$app->request->get('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->get('user_type') == 'student'){
        $studio_id=User::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }else{
        $studio_id=Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }

      if($studio_id == 0){
            $studio_id = Yii::$app->request->get('studio_id');
          }
      $page = empty(Yii::$app->request->get('page'))?0:Yii::$app->request->get('page');
      $limit = empty(Yii::$app->request->get('limit'))?5:Yii::$app->request->get('limit');
      $offset = $page*$limit;

      $comment = DailyComment::find()->where(['status'=>1,'studio_id'=>$studio_id,'daily_id'=>Yii::$app->request->get('id'),'daily_comment_pid'=>0])->offset($offset)->limit($limit)->orderby('(case when user_id='.Yii::$app->request->get('user_id').' then 1 else 0 end) DESC, daily_comment_id ASC')->all();
      
      $res['comment_list'] = array();
      //动态信息
      $daily = Daily::findOne(Yii::$app->request->get('id'));

      $image_url_came = array();


      $is_parent = 0;
      if($daily->user_type == 'teacher'){
        $user = Admin::findOne($daily->user_id);
      }else if($daily->user_type  == 'student'){
        $user = User::findOne($daily->user_id);
      }else{
        $is_parent = 1;
        $user = Family::findOne($daily->user_id);
      }

      if(!empty($daily->image_url_came)){
        $arr = explode(',',$daily->image_url_came);
        foreach ($arr as $k => $v) {
          $temp = explode("?", $v);
            $image_url_came[$k]['url'] = $temp[0].'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c';
            $image_url_came_thumb[$k]['url'] = $temp[0].'?x-oss-process=style/thumb';
        }
      }
      
      //关注相关数据
      $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'follow_user_id'=>$daily->user_id,'follow_user_type'=>$daily->user_type,'status'=>1]);
      $follow_num = UserFollow::find()->where(['follow_user_id'=>$daily->user_id,'follow_user_type'=>$daily->user_type,'status'=>1])->count();

      //点赞相关数据
      $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'like_type'=>'daily','like_id'=>$daily->daily_id,'status'=>1]);
      $like_num = UserLike::find()->where(['like_type'=>'daily','like_id'=>$daily->daily_id,'status'=>1])->count();

      $like_user = UserLike::find()->where(['like_type'=>'daily','like_id'=>$daily->daily_id,'status'=>1])->orderby('timer desc')->limit(10)->all();
      
      $res['like_user'] = array();
      foreach ($like_user as $key => $value) {
        $like_user_info = array();

        if($value->user_type == 'teacher'){
          $like_user_info['id'] = $value->user_id;
          $like_user_info['user_type'] = 'teacher';
          $like_user_info['name'] = Admin::findOne($value->user_id)->name;
          $like_user_info['avatar'] = empty(Admin::findOne($value->user_id)->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',Admin::findOne($value->user_id)->image).'?x-oss-process=style/57x57';
        }else if($value->user_type  == 'student'){
          $like_user_info['id'] = $value->user_id;
          $like_user_info['user_type'] = 'student';
          $like_user_info['name'] = User::findOne($value->user_id)->name;
          $like_user_info['avatar'] = empty(User::findOne($value->user_id)->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',User::findOne($value->user_id)->image).'?x-oss-process=style/57x57';
        }else{
          $like_user_info['id'] = $value->user_id;
          $like_user_info['user_type'] = 'family';
          $like_user_info['name'] = Family::findOne($value->user_id)->name;
          $like_user_info['avatar'] = empty(Family::findOne($value->user_id)->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',Admin::findOne($value->user_id)->image).'?x-oss-process=style/57x57';
        }

        $res['like_user'][$key] = $like_user_info;
      }
      //是否激活
      $is_activating = ActivationCode::findOne(['relation_id'=>$daily->user_id,'type'=>($daily->user_type == 'teacher'?1:($daily->user_type  == 'student'?2:0)),'is_active'=>10,'status'=>10]);
      $res['daily'] = array(
            'id'=> $daily->daily_id, 
            'timer' => $daily->timer*1000,
            'avatar' => empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).'?x-oss-process=style/57x57',
            'name' => $user->name,
            'follow_user_id' => $daily->user_id,
            'addres' => '',
            'status' => $daily->user_type == 'teacher'?'老师':($daily->user_type == 'family'?'家长':'学生'),
            'follow_user_type' => $daily->user_type,
            'is_follow' => !empty($is_follow)?true:false,//是否关注
            'follow' => (int)$follow_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'is_activating' => !empty($is_activating)?true:false,//教师是否激活
            'is_parent' => !empty($is_parent)?true:false,
            'is_top' => $daily->top_time==0?false:true,
            'content'=> $daily->content,//文字
            'image_url_came' => $image_url_came,//图片
            'views' => $daily->views,//查看数
            'image_url_came_thumb' => $image_url_came_thumb,//图片
            'url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->like_id,
            'comment'=> (int)DailyComment::find()->where(['status'=>1,'daily_id'=>$daily->daily_id])->count(1),//评论数
            'like' => (int)$like_num,//点赞
            'share'=> array(
              'share_title'=>'云校分享',
              'share_desc'=>$daily->content,
              'share_url'=>'https://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->like_id.'&is_banner=1',
              'share_image'=>empty($image_url_came)?(empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original']):$image_url_came[0]['url'],
            )
        );
        $res['new_list'] = $this->GetNewList($studio_id,0,4);

      if(!empty($comment)){
        foreach ($comment as $key => $value) {
          if($value->user_type == 'teacher'){
            $user = Admin::findOne($value->user_id);
          }else if($value->user_type  == 'student'){
            $user = User::findOne($value->user_id);
          }else{
            $user = Family::findOne($value->user_id);
          }
          $rescomment = $this->getcomment($studio_id,$daily->daily_id,$value->daily_comment_id,Yii::$app->request->get('user_id'),Yii::$app->request->get('user_type'));

          $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1]);
          $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$value->daily_comment_id,'status'=>1])->count();
          $res['comment_list'][$key] =  array(
            'id' => $value->daily_comment_id,
            'user_id' => $user->id,
            'user_type' => $value->user_type,
            'like_num' => (int)$like_num,
            'is_like' => !empty($is_like)?true:false,//是否点赞
            'name' => $user->name,
            'avatar' =>empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original'],
            'timer' => $value->timer*1000,
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
    }
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  //获取评论下级评论详情
  public function actionGetComment(){
    if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('id'))){
      if(Yii::$app->request->post('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->post('user_type') == 'student'){
        $studio_id= User::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }else{
        $studio_id=Family::findOne(Yii::$app->request->post('user_id'))->studio_id;
      }
      if($studio_id == 0){
            $studio_id = Yii::$app->request->post('studio_id');
          }


      $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
      $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
      $offset = $page*$limit;
      $comment = DailyComment::find()->where(['status'=>1,'daily_comment_id'=>Yii::$app->request->post('id')])->one();

      $rescomment = $this->getcomment($studio_id,$comment->daily_id,Yii::$app->request->post('id'),Yii::$app->request->post('user_id'),Yii::$app->request->post('user_type'),$limit,$offset);

      if($comment->user_type == 'teacher'){
        $user = Admin::findOne($comment->user_id);
      }else if($comment->user_type  == 'student'){
        $user = User::findOne($comment->user_id);
      }else{
        $user = Family::findOne($comment->user_id);
      }

      $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'like_type'=>'daily_comment','like_id'=>$comment->daily_comment_id,'status'=>1]);
      $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$comment->daily_comment_id,'status'=>1])->count();
      $res['comment'] =  array(
        'id' => $comment->daily_comment_id,
        'user_id' => $comment->user_id,
        'user_type' => $comment->user_type,
        'like_num' => (int)$like_num,
        'is_like' => !empty($is_like)?true:false,//是否点赞
        'name' => $user->name,
        'avatar' =>empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original'],
        'timer' => $comment->timer*1000,
        'content' => $comment->content,
      );
      $res['reply_comment'] = $rescomment;
      return [
                'success' => true,
                'data' => $res,
                'message' => '获取数据成功'
            ];
    }else{
          if( !empty(Yii::$app->request->get('user_type')) && !empty(Yii::$app->request->get('user_id')) && !empty(Yii::$app->request->get('id'))){
      if(Yii::$app->request->get('user_type') == 'teacher'){
        $studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->get('user_id'))->campus_id)->studio_id;
      }else if(Yii::$app->request->get('user_type') == 'student'){
        $studio_id= User::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }else{
        $studio_id=Family::findOne(Yii::$app->request->get('user_id'))->studio_id;
      }
      if($studio_id == 0){
            $studio_id = Yii::$app->request->get('studio_id');
          }


      $page = empty(Yii::$app->request->get('page'))?0:Yii::$app->request->get('page');
      $limit = empty(Yii::$app->request->get('limit'))?5:Yii::$app->request->get('limit');
      $offset = $page*$limit;
      $comment = DailyComment::find()->where(['status'=>1,'daily_comment_id'=>Yii::$app->request->get('id')])->one();

      $rescomment = $this->getcomment($studio_id,$comment->daily_id,Yii::$app->request->get('id'),Yii::$app->request->get('user_id'),Yii::$app->request->get('user_type'),$limit,$offset);

      if($comment->user_type == 'teacher'){
        $user = Admin::findOne($comment->user_id);
      }else if($comment->user_type  == 'student'){
        $user = User::findOne($comment->user_id);
      }else{
        $user = Family::findOne($comment->user_id);
      }

      $is_like = UserLike::findOne(['user_id'=>Yii::$app->request->get('user_id'),'user_type'=>Yii::$app->request->get('user_type'),'like_type'=>'daily_comment','like_id'=>$comment->daily_comment_id,'status'=>1]);
      $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$comment->daily_comment_id,'status'=>1])->count();
      $res['comment'] =  array(
        'id' => $comment->daily_comment_id,
        'user_id' => $comment->user_id,
        'user_type' => $comment->user_type,
        'like_num' => (int)$like_num,
        'is_like' => !empty($is_like)?true:false,//是否点赞
        'name' => $user->name,
        'avatar' =>empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original'],
        'timer' => $comment->timer*1000,
        'content' => $comment->content,
      );
      $res['reply_comment'] = $rescomment;
      return [
                'success' => true,
                'data' => $res,
                'message' => '获取数据成功'
            ];
    }
      return [
              'success' => false,
              'message' => '非POST传值或缺少参数'
          ];
    }
  }

  public function getcomment($studio_id,$daily_id,$daily_comment_id,$user_id,$user_type,$limit=5,$offset=0){
    $rescomments= DailyComment::find()->where(['status'=>1,'studio_id'=>$studio_id,'daily_id'=>$daily_id,'daily_comment_pid'=>$daily_comment_id])->offset($offset)->limit($limit)->orderby('(case when (user_id='.$user_id.' AND user_type="'.$user_type.'") or (reply_user_id='.$user_id.' AND reply_user_type="'.$user_type.'") then 1 else 0 end) DESC, daily_comment_id ASC')->all();
    $res = array();
    foreach ($rescomments as $k => $v) {
      unset($reply_user);
      if(!empty($v->reply_user_type) && !empty($v->reply_user_id)){
        if($v->reply_user_type == 'teacher'){
          $reply_user = Admin::findOne($v->reply_user_id);
        }else if($v->reply_user_type  == 'student'){
          $reply_user = User::findOne($v->reply_user_id);
        }else{
          $reply_user = Family::findOne($v->reply_user_id);
        }
      }
      
      $is_like = UserLike::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'like_type'=>'daily_comment','like_id'=>$v->daily_comment_id,'status'=>1]);

      if($v->user_type == 'teacher'){
        $user = Admin::findOne($v->user_id);
      }else if($v->user_type  == 'student'){
        $user = User::findOne($v->user_id);
      }else{
        $user = Family::findOne($v->user_id);
      }
      $like_num = UserLike::find()->where(['like_type'=>'daily_comment','like_id'=>$v->daily_comment_id,'status'=>1])->count();
      $res[] =  array(
        'id' => $v->daily_comment_id,
        'user_id' => $v->user_id,
        'user_type' => $v->user_type,
        'like_num' => (int)$like_num,
        'is_like' => !empty($is_like)?true:false,//是否点赞
        'name' => $user->name,
        'avatar' =>empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($studio_id,'picture','image',$user->image).Yii::$app->params['oss']['Size']['original'],
        'timer' => $v->timer*1000,
        'content' => $v->content,
        'reviewers' => !empty($reply_user)?$reply_user->name:'',
      );


    }
    return $res;
  }



    //定时计划 推送
    public function actionUpdatePush(){

        $newlists = NewList::find()->where('timing_push_time is not null AND status = 10')
                                 ->all();

        if(!empty($newlists)){
            foreach ($newlists as $newlist) {
                $time = time();
                $timing_push_time = strtotime($newlist->timing_push_time);
                if($time >= $timing_push_time  && $time - $timing_push_time < 86400){
                    $this->NewListPush($newlist->new_list_id);
                }
            }
        }
    }
    //推送
    public function NewListPush($id){
        $newlists = NewList::findOne(['new_list_id' => $id,'status' => 10]);
        if(!empty($newlists)){
            //美术世界推送
          $url = empty($newlists->url)?'https://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id='.$newlists->new_list_id:$newlists->url;
           PostPush::PushMsg($newlists->name,$newlists->name,'最新新闻资讯','news',$newlists->new_list_id,$url);
             $newlists->timing_push_time = NULL;
             $newlists->is_push = 1;
             if($newlists->save()){
                 echo "新闻ID:".$newlists->new_list_id.",推送时间:".date("Y-m-d H:i:s",time()).",目前正在推送中\r\n";
             }
        }
    }
}