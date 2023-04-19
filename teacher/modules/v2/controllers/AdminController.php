<?php
namespace teacher\modules\v2\controllers;

use Yii;
use components\Code;
use components\Alidayu;
use common\models\Format;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Tencat;
use teacher\modules\v2\models\Family;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\Picture;
use teacher\modules\v2\models\CodeAdmin;
use teacher\modules\v2\models\ChatAdmin;
use teacher\modules\v2\models\LoginForm;
use teacher\modules\v2\models\Invitation;
use teacher\modules\v2\models\SimpleUser;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;


class AdminController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Admin';
    
    /*
    *[actionLogin]  登陆接口
    *@param 
    *
    */

    public function actionLoginTest() 
    {
        error_reporting(0); 
        if($studio_name = Yii::$app->request->post('studio_name')) {
            $studio_id = Studio::findOne(['name'=>$studio_name])->id;
        }
        if(Yii::$app->request->post('studio_id')) {
            $studio_id = Yii::$app->request->post('studio_id');
        }

        $model = new LoginForm();

        $code          =  new Code();

        $token_value   =  $code->CreateToken($studio_id);

        if(Yii::$app->request->post('landing') == $model::LANDING_CODE) {
           $model->scenario = 'code';
        }else if(Yii::$app->request->post('landing') == $model::LANDING_PHONE){
           // if($studio_id == 103) {
           //      return SendMessage::sendErrorMsg("不能注册!");
           // }
           $model->scenario = 'phone';
        }

        /* 二维码邀请 */
        
        $invite_id          = Yii::$app->request->post('invite_id');

        $invite_role        = Yii::$app->request->post('role');


        $lan  = "您的好友已邀请成功，特为您呈上免费7天会员。邀请不停，赠送不停！";

        $bei  = "恭喜您注册成功，特为您呈上免费7天会员。";


        /* 二维码邀请end */
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $model->studio_id = $studio_id;
       
        if(Yii::$app->request->post('type') == ActivationCode::TYPE_TEACHER) {
            if($admin = $model->login()) {
                if(!$admin->usersig) {
                    Tencat::Create('teacher'.$admin->id,'test');

                    if($admin->name){
                        Tencat::UpdateName('teacher'.$admin->id,$admin->name);
                    }else{
                        Tencat::UpdateName('teacher'.$admin->id,'未设置');
                    }

                    if($admin->image){
                        Tencat::CreateImage('teacher'.$admin->id,Picture::getImage($admin->id,$admin->image,Yii::$app->request->post('type')));
                    }else{
                        Tencat::CreateImage('teacher'.$admin->id,"http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c");
                    }
                }
                $role = Yii::$app->authManager->getRolesByUser($admin->id);
                $usersig = Tencat::CreateNumber('teacher'.$admin->id);
                $admin->role = $role[key($role)]->name;
                $admin->usersig     = current($usersig);
                $admin->token_value  = $token_value;

                //二维码邀请增加记录
                if(in_array($studio_id, array(183))) {
                    if($admin->is_first == 0 && $invite_id && $invite_role && Yii::$app->request->post('landing') == $model::LANDING_PHONE) {
                          Invitation::CreateOne($invite_id,$invite_role,$admin->id,10);
                    }
                }

                $admin->is_first += 1;

                //二维码邀请增加天数
                if(in_array($studio_id, array(183))) {
                    if(!$invite_id && !$invite_role && Yii::$app->request->post('landing') == $model::LANDING_PHONE) {
                        $Invitation = Invitation::findOne(['invitee_id'=>$admin->id,'invitee_role'=>10,'status'=>10]);
                        if($Invitation) {
                                 Invitation::AddDays($Invitation->id);
                        }
                    }
                }
                if($admin->save()) {
                    $_GET['message'] = Yii::t('teacher', 'Login Success');
                    if($studio_name){
                        $admin->studio_name = Studio::findOne($admin->studio_id)->name;
                    }

                    return $admin;
                }else{
                    return SendMessage::sendErrorMsg("激活码不可用或已过期");
                }
            }else{
                return SendMessage::sendErrorMsg(current($model->getErrors())[0]);
            }
        }else if(Yii::$app->request->post('type') == ActivationCode::TYPE_USER){

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $model->studio_id = $studio_id;
            if($user = $model->login()) {
              # $user->studio_id = $studio_id;
                if(!$user->usersig) {
                     Tencat::Create('student'.$user->id,'test');
                     if($user->name){
                         Tencat::UpdateName('student'.$user->id,$user->name);
                     }else{
                         Tencat::UpdateName('student'.$user->id,'未设置');
                     }

                    if(!$user->image){
                        Tencat::CreateImage('student'.$user->id,Picture::getImage($user->id,$user->image,Yii::$app->request->post('type')));
                    }else{
                        Tencat::CreateImage('student'.$user->id,"http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c");
                    }
                }
                $usersig = Tencat::CreateNumber('student'.$user->id);

                $user->usersig = current($usersig);

                $user->token_value = $token_value;

                
                if(Yii::$app->request->post('landing') == $model::LANDING_PHONE) {
                    if(!$user->codes && $user->is_first == 0 && in_array($studio_id, array(43,183))) {
                        $account = 'student'.$user->id;
                         Tencat::SendMsg(array($account),Yii::$app->params['XinXi']);
                        //二维码邀请增加记录
                        if($invite_id && $invite_role ) {
                              Invitation::CreateOne($invite_id,$invite_role,$user->id,20);
                        } 
                    }
                }


                #file_put_contents(Yii::$app->basePath."/data.txt", "id=$invite_id----role=$invite_role\n", FILE_APPEND);

                $user->is_first  += 1;

                if($user->save()) {
                    $_GET['message'] = Yii::t('teacher', 'Login Success');
                    $user =  SimpleUser::find()
                                       ->select(['admin_id'=>'id','phone_number','studio_id','name','campus_id','class_id','usersig','gender','contact_phone','national_id','province','grade','school_name','graduation_at','total_score','image','token_value','is_image','vip_time'])
                                       ->where(['id'=>$user->id])
                                       ->one();
                    if($studio_name){
                        $user->studio_name = Studio::findOne($user->studio_id)->name;
                    }


                    //二维码邀请增加天数
                    if(in_array($studio_id, array(183))) {
                        if(!$invite_id && !$invite_role && Yii::$app->request->post('landing') == $model::LANDING_PHONE) {
                            $Invitation = Invitation::findOne(['invitee_id'=>$user->admin_id,'invitee_role'=>20,'status'=>10]);
                            if($Invitation) {
                                     Invitation::AddDays($Invitation->id);
                            }
                        }
                    }

                    return $user;
                }else{
                    return SendMessage::sendErrorMsg('登陆失败!');
                }
               
            }else{
                return SendMessage::sendErrorMsg(current($model->getErrors())[0]);
            }
        }else if(Yii::$app->request->post('type') ==ActivationCode::TYPE_FAMILY) {

            if($family = $model->login()) {
                if(!$family->usersig) {
                    Tencat::Create('family'.$family->id,'test');

                    if($family->name){
                        Tencat::UpdateName('family'.$family->id,$family->name);
                    }else{
                        Tencat::UpdateName('family'.$family->id,'未设置');
                    }

                    if(!$family->image){
                        Tencat::CreateImage('family'.$family->id,Picture::getImage($family->id,$family->image,Yii::$app->request->post('type')));
                    }else{
                        Tencat::CreateImage('family'.$family->id,"http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c");
                    }
                }
                $usersig = Tencat::CreateNumber('family'.$family->id);

                $family->studio_id = $studio_id;

                $family->usersig = current($usersig);

                $family->token_value = $token_value;

                //二维码邀请增加记录
                if(in_array($studio_id, array(183))) {
                    if($family->is_first == 0 && $invite_id && $invite_role && Yii::$app->request->post('landing') == $model::LANDING_PHONE) {
                          Invitation::CreateOne($invite_id,$invite_role,$family->id,30);
                    }
                }

                $family->is_first += 1;

                if($family->save()) {
                    $_GET['message'] = Yii::t('teacher', 'Login Success');
                    $family = Family::findOne($family->id);
                    if($studio_name){
                        $family->studio_name = $studio_name;
                    }


                    //二维码邀请增加天数
                    if(in_array($studio_id, array(183))) {
                        if(!$invite_id && !$invite_role && Yii::$app->request->post('landing') == $model::LANDING_PHONE) {
                            $Invitation = Invitation::findOne(['invitee_id'=>$family->id,'invitee_role'=>30,'status'=>10]);
                            if($Invitation) {
                                 Invitation::AddDays($Invitation->id);
                            }
                        }
                    }
                    return $family;
                }else{
                    return SendMessage::sendErrorMsg("登陆失败!");
                }
               
            }else{
                return SendMessage::sendErrorMsg(current($model->getErrors())[0]);
            }

        }

         
    }

    //发送手机验证码
    public function actionSendPhoneVerifyCode($phone_number,$isArtWorld = 0)
    {

        if(!Format::isMobile()) {
            return SendMessage::sendErrorMsg("短信发送失败!");
        }

        $alidayu = new Alidayu();
        
        $name = ($isArtWorld == 0) ? "云校美术" : "美术世界APP";

        $res = $alidayu->sendPhoneVerifyCode($phone_number,$name);

        if($res != false){
            return SendMessage::sendSuccessMsg(Yii::t('api', 'Verify Code Send Success'));
        }else{
            return SendMessage::sendSuccessMsg(Yii::t('api', 'Verify Code Send Success'));
        }
    }
    /**
     * [退出登陆]
     *
     * @param $id 用户id
     *
     */
    public function actionLogout($admin_id)
    {
        $model = $this->findModel($admin_id);
        if($model){
            return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Logout Success'));
        }else{
            return SendMessage::sendErrorMsg(Yii::t('teacher', 'User Not Exist'));
        }
    }

    /*
     *[获取可见范围用户信息]
     *
     *
    */
    public function actionGetList($admin_id)
    {
        $modelClass = $this->modelClass;

        $list = $modelClass::getVisua($admin_id);

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        
        return $list;
    }

    //教师管理详情
    public function actionGetInfo() {
        error_reporting(0); 
       $admin_id =  Yii::$app->request->post('admin_id');

       $info     =  CodeAdmin::findOne($admin_id);


       if(in_array($info->studio_id,array(103,183))) {

           $button   =  array(
                            array(
                                'title'    => '是否具有招生权限',
                                'type'     => 'is_chat',
                                'isShowArrow' => true,
                                'subtitle' => $info['is_chat'] ? '能' : '否',
                                'image' => NULL
                            ),
                            array(
                                'title'    => '能否查看购买的云课件',
                                'type'     => 'is_cloud',
                                'isShowArrow' => true,
                                'subtitle' => $info['is_cloud'] ? '能' : '否',
                                'image' => NULL
                            ),
                            array(
                                'title'    => '只看自己课件',
                                'type'     => 'is_all_visible',
                                'isShowArrow' => true,
                                'subtitle' => $info['is_all_visible'] ? '否' : '是',
                                'image' => NULL
                            ),
                            array(
                                'title'    => '能否创建课件',
                                'type'     => 'is_create',
                                'isShowArrow' => true,
                                'subtitle' => $info['is_create'] ? '是' : '否',
                                'image' => NULL
                            ),
                        );
       }else{
           $button   =  array(
                            array(
                                'title'    => '是否具有招生权限',
                                'type'     => 'is_chat',
                                'isShowArrow' => true,
                                'subtitle' => $info['is_chat'] ? '能' : '否',
                                'image' => NULL
                            ),
                            array(
                                'title'    => '能否查看购买的云课件',
                                'type'     => 'is_cloud',
                                'isShowArrow' => true,
                                'subtitle' => $info['is_cloud'] ? '能' : '否',
                                'image' => NULL
                            )
                        );
       }

       $role = current(Yii::$app->authManager->getRolesByUser($info['id']))->description;



       if(!$info->codes) {
           $class = "散户老师全部不可见";
       }else{
            $class = $info['class_id'] ? \teacher\modules\v2\models\Classes::getName($info['class_id']) : "全部可见";
       }

       $_GET['message'] = Yii::t('teacher','Sucessfully Get List');
       return array(
                array(
                    array(

                        'title'    => '姓名',
                        'type'     => 'name',
                        'isShowArrow' => false,
                        'subtitle' => $info['name'],
                        'image' => CodeAdmin::getImage($info['id'],$info['image']),
                    )
                ),
                array(
                    array(
                        'title'    => '角色',
                        'type'     => 'role',
                        'isShowArrow' => true,
                        'subtitle' => $role,
                        'image' => NULL
                    )
                ),
                array(
                    array(
                        'title'    => '可见科目',
                        'type'     => 'category_id',
                        'isShowArrow' => true,
                        'subtitle' =>  $info['category_id'] ? \teacher\modules\v2\models\CodeCategory::getName($info['category_id']) : "全部可见",
                        'image' => NULL
                    ),
                    array(
                        'title'    => '可见校区',
                        'type'     => 'campus_id',
                        'isShowArrow' => true,
                        'subtitle' => $info['campus_id'] ? \teacher\modules\v2\models\CodeCampus::getName($info['campus_id']) : "全部不可见",
                        'image' => NULL
                    ),
                    array(
                        'title'    => '可见班级',
                        'type'     => 'class_id',
                        'isShowArrow' => false,
                        'subtitle' => $class,
                        'image' => NULL
                    )
                ),
                $button
                ,
                array(
                    array(
                        'title'    => '激活码',
                        'type'     => 'code_number',
                        'isShowArrow' => false,
                        'subtitle' => $info->codes->code ? $info->codes->code : "未设置",
                        'image' => NULL
                    )
                ),
            );
    }

    //修改角色
    public function actionTeacherInfoUpdate() {

        $login_id = Yii::$app->request->post('login_id');

        // $item_name =  CodeAdmin::findOne($login_id)->auths->item_name;

        // $pid =  \teacher\modules\v2\models\AuthAssignment::find()->where(['item_name'=>$item_name])->one()->rbacs->pid;

        // if($pid != 0) {
        //     return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
        // }
        $studio =  CodeAdmin::findOne($login_id)->studio_id;

        $item_name =  CodeAdmin::findOne($login_id)->auths->item_name;

        $pid = substr($item_name,-3);

        // if(!in_array($pid,Yii::$app->params['Shenfen'])) {
        //     return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
        // }


        if(!in_array($studio,Yii::$app->params['Studio'])) {

            if(!in_array($pid,Yii::$app->params['OhterShenfen'])) {
                return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
            }
        }else{
            if(!in_array($pid,Yii::$app->params['Shenfen'])) {
                return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
            }
        }
        $admin_id = Yii::$app->request->post('admin_id');

        $model = $this->findModel($admin_id);

        $role     = Yii::$app->request->post('role');

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        if($role){
            $model->role = $role;
        }else{
            $role = Yii::$app->authManager->getRolesByUser($model->id);
            $model->role = $role[key($role)]->name;
        }


        if(Yii::$app->request->post('class_id')){
            $class = Format::explodeValue(Yii::$app->request->post('class_id'));
        }else{
            $class = Format::explodeValue($model->class_id);
        }
        $new_class = array();
        if($class){
            foreach($class as $key => $value) {
                if($value >= 1) {
                    $new_class[] = $value;
                } 
            }
        }
        $class_id = Format::implodeValue($new_class);
        if($class_id){
            $model->class_id = $class_id;
        }else{
            $model->class_id = NULL;
        }

        if($model->save()){
            return SendMessage::sendSuccessMsg("修改成功");
        }else{
            return SendMessage::sendErrorMsg("修改失败");
        }
    }

    public function actionCustomer() {

        $array = Yii::$app->params['KeFu'];

        $phone = array_rand($array);

        $list  = array($array[$phone]);

        $_GET['message'] = '获取成功';

        $url      =  "http://backend.meishuquanyunxiao.com/wenti.html";
        $Customer =  $this->GetTeachers($list);

        return array(
                'url'      => $url,
                'customer' => $Customer,
                'phone'    => $phone,
        );
    }


    public function GetTeachers($ids) {
        $teachers = ChatAdmin::find()
                            ->where(['id'=>$ids])
                            ->one();
        return $teachers;
    }

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}