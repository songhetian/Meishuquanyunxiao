<?php
namespace api\modules\v1\controllers;

use Yii;
use components\Alidayu;
use components\Oss;
use backend\models\Admin;
use common\models\User;
use teacher\modules\v2\models\Family;
use common\models\DailyComment;
use common\models\UserLike;
use common\models\Daily;
use common\models\UserFollow;
use common\models\Campus;
use common\models\LoginForm;
use teacher\modules\v2\models\Invitation;
use common\models\ResetPasswordForm;
use common\models\ForgetPasswordForm;
use common\models\UpdatePhoneNumberForm;
use api\modules\v1\models\SendMessage;
use common\models\InviteSell;

class UserController extends MainController
{
    public $modelClass = 'api\modules\v1\models\User';


    //推广人首页返回
    public function actionPayOrder(){
        switch (Yii::$app->request->get('user_type')) {
            case 'teacher':
                $admin_role = 10;
                $admin = Admin::findOne(Yii::$app->request->get('user_id'));
                break;
            case 'student':
                $admin_role = 20;
                $admin = User::findOne(Yii::$app->request->get('user_id'));
                break;
            case 'family':
                $admin_role = 30;
                $admin = Family::findOne(Yii::$app->request->get('user_id'));
                break;
        }

        $dirPeople = Invitation::find()->select("count(1) as status")->where("invite_id = ".Yii::$app->request->get('user_id')." AND role = ".$admin_role)->one();


        $sum = InviteSell::find()->select("SUM(price) AS price")->where("status = 1 AND CONCAT(user_id,user_type) IN (SELECT CONCAT(invitee_id,(case invitee_role WHEN 10 THEN 'teacher' WHEN 20 THEN 'student' WHEN 30 THEN 'family' END ) )  FROM invitation WHERE invite_id = ".Yii::$app->request->get('user_id')." AND role = ".$admin_role."  )")->one();

        $arr["payInfo"]=array(
        "dirPeople"=>$dirPeople->status,
        "indPeople"=>0,
        "payMon"=>(($sum->price*$admin->sell_num)/10000));

        $arr["toolList"]=array(
            array("id"=>1,"name"=>'全部'),
            array("id"=>2,"name"=>'会员推广'),
            array("id"=>3,"name"=>'电子书'),
            array("id"=>4,"name"=>'收费课程'),
            array("id"=>5,"name"=>'云课件'),
            array("id"=>6,"name"=>'我的云课件'),
            array("id"=>7,"name"=>'我的课程')
        );

        return $arr;

    }

    public function actionPayList(){

        //购买用户
        switch (Yii::$app->request->get('user_type')) {
            case 'teacher':
                $admin_role = 10;
                $admin = Admin::findOne(Yii::$app->request->get('user_id'));
                break;
            case 'student':
                $admin_role = 20;
                $admin = User::findOne(Yii::$app->request->get('user_id'));
                break;
            case 'family':
                $admin_role = 30;
                $admin = Family::findOne(Yii::$app->request->get('user_id'));
                break;
        }

        $str = InviteSell::find()->where("CONCAT(user_id,user_type) IN (SELECT CONCAT(invitee_id,(case invitee_role WHEN 10 THEN 'teacher' WHEN 20 THEN 'student' WHEN 30 THEN 'family' END ) )  FROM invitation WHERE invite_id = ".Yii::$app->request->get('user_id')." AND role = ".$admin_role."  )")->all();

        $pay_list = array();
        foreach ($str as $key => $value) {

            $isPay = "";
            $icon = "";
           
            //购买用户
            switch ($value->user_type) {
                case 'teacher':
                    $userrole = "老师";
                    $user = Admin::findOne($value->user_id);
                    break;
                case 'student':
                    $userrole = "学生";
                    $user = User::findOne($value->user_id);
                    break;
                case 'family':
                    $userrole = "家长";
                    $user = Family::findOne($value->user_id);
                    break;
            }

            switch ($value->buy_type) {
                case 'ios':
                    $buytype = "苹果";
                    break;
                case 'wechat':
                    $buytype = "微信";
                    break;
                case 'alipay':
                    $buytype = "支付宝";
                    break;
            }


            //订单状态
            switch ($value->status) {
                case 0:
                    $isPay = "未审核";
                    break;
                case 1:
                    $isPay = "可提现";
                    break;
                case 2:
                    $isPay = "已提现";
                    break;
            }
            //分类图标
            switch ($value->goods_type) {
                case 'vip':
                    $icon = "https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/pay/会员推广.png?x-oss-process=style/logo";
                    break;
                case 'ebook':
                    $icon = "https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/pay/电子书.png?x-oss-process=style/logo";
                    break;
                case 'course':
                    $icon = "https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/pay/收费课程.png?x-oss-process=style/logo";
                    break;
            }
            $pay_list[$key]['id'] = $value->id;
            $pay_list[$key]['name'] = $user->name."(".$userrole." ".$buytype.")";
            $pay_list[$key]['payDate'] = $value->time;
            $pay_list[$key]['payMon'] = "￥".(($value->price*$admin->sell_num)/10000);
            $pay_list[$key]['payOr'] = "￥".($value->price/100);
            $pay_list[$key]['payUse'] = $value->name;
            $pay_list[$key]['isPay'] = $isPay;
            $pay_list[$key]['icon'] = $icon;
        }

        


        $arr["inComeList"] = array(
            array(
                "id"=>1,
                "name"=>'本月',
                "payIn"=>8999,
                "payList"=>$pay_list,
            ),
        );
        return $arr;
    }

    //关注
    public function actionUserFollow(){
        if( !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) && !empty(Yii::$app->request->post('follow_user_type')) && !empty(Yii::$app->request->post('follow_user_id'))){
          $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'follow_user_type'=>Yii::$app->request->post('follow_user_type'),'follow_user_id'=>Yii::$app->request->post('follow_user_id')]);
          if(!empty($is_follow)){
            $is_follow->status==1?$is_follow->status=2:$is_follow->status=1;
              if($is_follow->save()){
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
            $user_follow = new UserFollow();
            $user_follow->user_type = Yii::$app->request->post('user_type');
            $user_follow->user_id = Yii::$app->request->post('user_id');
            $user_follow->follow_user_type = Yii::$app->request->post('follow_user_type');
            $user_follow->follow_user_id = Yii::$app->request->post('follow_user_id');
            $user_follow->timer = time();
            if(Yii::$app->request->post('user_type') == 'teacher'){
              $user_follow->studio_id = Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
            }else if(Yii::$app->request->post('user_type') == 'student'){
              $user_follow->studio_id = Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
            }else{
              $user_follow->studio_id = Campus::findOne(Family::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
            }
            if($user_follow->studio_id == 0){
              $user_follow->studio_id = Yii::$app->request->post('studio_id');
            }
            if($user_follow->save()){
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
    //个人空间
    public function actionGetUserSpace(){
        if( !empty(Yii::$app->request->post('look_user_type')) && !empty(Yii::$app->request->post('look_user_id')) && !empty(Yii::$app->request->post('user_type')) && !empty(Yii::$app->request->post('user_id')) ){
            $page = empty(Yii::$app->request->post('page'))?0:Yii::$app->request->post('page');
            $limit = empty(Yii::$app->request->post('limit'))?5:Yii::$app->request->post('limit');
            $offset = $page*$limit;
            $res = array();
            
            $information = array();

            $is_chat = false;

            if(Yii::$app->request->post('user_type') == 'teacher'){
                $is_chat_u = Admin::findOne(Yii::$app->request->post('user_id'));
                $is_chat = $is_chat_u==1?true:false;
            }

            $timekey = 0;
            //公共数据
            if(Yii::$app->request->post('look_user_type') == 'teacher'){
                $user= Admin::findOne(Yii::$app->request->post('look_user_id'));
            }else if(Yii::$app->request->post('look_user_type') == 'student'){
                $user= User::findOne(Yii::$app->request->post('look_user_id'));
            }else{
                $user= Family::findOne(Yii::$app->request->post('look_user_id'));
            }
            $is_follow = UserFollow::findOne(['user_id'=>Yii::$app->request->post('user_id'),'user_type'=>Yii::$app->request->post('user_type'),'follow_user_id'=>Yii::$app->request->post('look_user_id'),'follow_user_type'=>Yii::$app->request->post('look_user_type'),'status'=>1]);

            $follow_num = UserFollow::find()->where(['follow_user_id'=>Yii::$app->request->post('look_user_id'),'follow_user_type'=>Yii::$app->request->post('look_user_type'),'status'=>1])->count();
            $attention_num = UserFollow::find()->where(['user_id'=>Yii::$app->request->post('look_user_id'),'user_type'=>Yii::$app->request->post('look_user_type'),'status'=>1])->count();
            $daliy_num = Daily::find()->where(['user_id'=>Yii::$app->request->post('look_user_id'),'user_type'=>Yii::$app->request->post('look_user_type'),'status'=>1])->count();
            //个人信息
            $information['name'] = $user->name;
            $information['usersig'] = $user->usersig;
            $information['id'] = Yii::$app->request->post('look_user_id');
            $information['user_role'] = Yii::$app->request->post('look_user_type');
            $information['is_chat'] = $is_chat;
            $information['identifier'] = Yii::$app->request->post('look_user_type').Yii::$app->request->post('look_user_id');
            $information['gender'] = '男';
            $information['avatar'] = empty($user->image)?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($user->studio_id,'picture','image',$user->image).'?x-oss-process=style/250x250';
            $information['isFollow'] = !empty($is_follow)?true:false;//是否关注
            $information['addres'] = '';
            $information['status'] = Yii::$app->request->post('look_user_type') == 'teacher'?'老师':($daily->user_type == 'family'?'家长':'学生');
            $information['followerNumber'] = $follow_num;//粉丝数
            $information['attentionNumber'] = $attention_num;//关注数
            $information['logNumber'] = $daliy_num;//日志数
            $res['information'] = $information;
            $res['sectionDataList'] = array();
            //状态列表
            $daily = Daily::find()->where(['status'=>1,'studio_id'=>$user->studio_id,'user_id'=>Yii::$app->request->post('look_user_id'),'user_type'=>Yii::$app->request->post('look_user_type')])->offset($offset)->limit($limit)->orderby('daily_id desc')->all();
            foreach ($daily as $key => $value) {
                $image_url_came = array();
                $is_del = false;
                if(Yii::$app->request->post('user_id') == $value->user_id && Yii::$app->request->post('user_type') == $value->user_type){
                  $is_del = true;
                }
                if($value->user_type == 'teacher'){
                  $is_del = true;
                  $is_parent = false;
                }else if($value->user_type  == 'student'){
                  $is_parent = false;
                }else{
                  $is_parent = true;
                }

                if(!empty($value->image_url_came)){
                  $arr = explode(',',$value->image_url_came);
                  foreach ($arr as $k => $v) {
                    $temp = explode("?", $v);
                    $image_url_came[$k]['url'] = $temp[0].'?x-oss-process=style/250x250';
                  }
                }
                $sectionDataList['id'] = $value->daily_id;
                $sectionDataList['timer'] = date("H:i",$value->timer);
                $sectionDataList['content'] = $value->content;
                $sectionDataList['image_url_came'] = $image_url_came;
                $sectionDataList['is_del'] = $is_del;
                $sectionDataList['comment'] = (int)DailyComment::find()->where(['status'=>1,'daily_id'=>$value->daily_id])->count(1);//评论数
                $sectionDataList['like'] = (int)UserLike::find()->where(['like_type'=>'daily','like_id'=>$value->daily_id,'status'=>1])->count();
                $sectionDataList['is_parent'] = $is_parent;
                $sectionDataList['views'] = $value->views;//查看数
                $sectionDataList['url'] = 'http://www.meishuquanyunxiao.com/share/daily-view.html?daily_id='.$value->daily_id;
                  
                if(!empty($res['sectionDataList'][$timekey]['title']) && $res['sectionDataList'][$timekey]['title'] != date("Y-m-d",$value->timer)){
                    $timekey++;
                    $res['sectionDataList'][$timekey]['data'][] = $sectionDataList;
                    $res['sectionDataList'][$timekey]['title'] = date("Y-m-d",$value->timer);
                }else{
                    $res['sectionDataList'][$timekey]['data'][] = $sectionDataList;
                    $res['sectionDataList'][$timekey]['title'] = date("Y-m-d",$value->timer);
                }
            }
            //相册
            $daily_img = Daily::find()->where(['status'=>1,'studio_id'=>Campus::findOne($user->campus_id)->studio_id,'user_id'=>Yii::$app->request->post('look_user_id'),'user_type'=>Yii::$app->request->post('look_user_type')])->andWhere("image_url_came is not NUll")->offset(0)->limit(3)->orderby('daily_id desc')->all();
            $hotDataList = array();
            if(!empty($daily_img)){
                foreach ($daily_img as $key => $value) {
                    if(!empty($value->image_url_came)){
                      $arr = explode(',',$value->image_url_came);
                      foreach ($arr as $k => $v) {
                        $temp = explode("?", $v);
                        $hotDataList[$k]['hotImage'] = $temp[0].'?x-oss-process=style/250x250';
                      }
                    }
                }
            }
            $res['hotDataList'] = $hotDataList;
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

    //发送手机验证码
    // public function actionSendPhoneVerifyCode($phone_number)
    // {
    //     $alidayu = new Alidayu();
    //     $res = $alidayu->sendPhoneVerifyCode($phone_number);
    //     if($res != false){
    //         return SendMessage::sendSuccessMsg(Yii::t('api', 'Verify Code Send Success'));
    //     }else{
    //         return SendMessage::sendErrorMsg(Yii::t('api', 'Verify Code Send Fail'));
    //     }
    // }

    //注册
    public function actionCreate()
    {
        $model = new User(['scenario' => 'alidayu']);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->save()) {
            return SendMessage::sendSuccessMsg(Yii::t('api', 'Register Success'));
        } else {
            return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }

    //登录
    public function actionLogin($studio_id)
    {
        $model = new LoginForm();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if($user = $model->login($studio_id)){
            if($user->is_review == User::REVIEW_NOT_YET){
                return SendMessage::sendErrorMsg(Yii::t('api', 'No REVIEW'));
            }
            if(!$user->class_id){
                return SendMessage::sendErrorMsg(Yii::t('api', 'No Class Assigned'));
            }
            $device_token = Yii::$app->request->post('device_token');

            $modelClass = $this->modelClass;
            if($modelClass::compareDeviceToken($user, $device_token)){
                $_GET['message'] = Yii::t('api', 'Login Success');
                return $this->findModel($user->id);
            }
        }
        return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }

    //根据手机号获取token值
    public function actionGetToken() {
        
       $modelClass = $this->modelClass;

       $device_token = $modelClass::find()->select('device_token')->where(Yii::$app->getRequest()->getBodyParams())->one();

       return $device_token;
    }

    //验证是否单点登陆
    public function actionTest($type) {

    }

    public function actionBindUser($phone_number)
    {
        $modelClass = $this->modelClass;
        $_GET['message'] = Yii::t('api', 'View Success');
        return $modelClass::findByPhoneNumber($phone_number);
    }

    //更新用户信息
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model){
            $model->setScenario('api_update');
            $model->load(Yii::$app->getRequest()->getBodyParams(), '');
            if ($model->save()) {
                $_GET['message'] = Yii::t('api', 'Update Success');
                return $model;
            } else {
                return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
            }
        }else{
            return SendMessage::sendErrorMsg(Yii::t('api', 'User Not Exist'));
        }
    }
   
    //修改密码
    public function actionResetPassword()
    {
        $model = new ResetPasswordForm();
        
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->resetPassword()) {
            return SendMessage::sendSuccessMsg(Yii::t('api', 'Reset Password Success'));
        }else{
           return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }

    //忘记密码
    public function actionForgetPassword($studio_id)
    {
        $model = new ForgetPasswordForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->forget($studio_id)) {
            return SendMessage::sendSuccessMsg(Yii::t('api', 'Forget Password Success'));
        }else{
           return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }

    //修改绑定手机
    public function actionUpdatePhoneNumber(){
        $model = new UpdatePhoneNumberForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($user = $model->updatePhoneNumber()) {
            $_GET['message'] = Yii::t('api', 'Update Phone Number Success');
            return $this->findModel($user->id);
        }else{
           return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }

    //退出
    public function actionLogout($id)
    {
        $model = $this->findModel($id);
        if($model){
            $model->device_token = NULL;
            if($model->save()){
                return SendMessage::sendSuccessMsg(Yii::t('api', 'Logout Success'));
            }else{
                return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
            }
        }else{
            return SendMessage::sendErrorMsg(Yii::t('api', 'User Not Exist'));
        }
    }

    protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}