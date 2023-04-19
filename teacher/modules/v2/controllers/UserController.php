<?php 
namespace teacher\modules\v2\controllers;


use Yii;
use common\models\User;
use common\models\Campus;
use components\Alidayu;
use yii\base\ErrorException;
use common\models\LoginForm;
use common\models\UserFollow;
use yii\data\ActiveDataProvider;
use common\models\Format;
use teacher\modules\v2\models\ChatAdmin;
use teacher\modules\v2\models\Family;
use common\models\ResetPasswordForm;
use common\models\ForgetPasswordForm;
use teacher\modules\v1\models\Errors;
use teacher\modules\v2\models\CodeUser;
use teacher\modules\v2\models\Studio;
use common\models\UpdatePhoneNumberForm;
use api\modules\v1\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;


class UserController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\User';

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
            if(Yii::$app->request->post('user_type')=='admin'){
              $user_follow->studio_id= Campus::findOne(Admin::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
            }else{
              $user_follow->studio_id= Campus::findOne(User::findOne(Yii::$app->request->post('user_id'))->campus_id)->studio_id;
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
    public function actionLogin()
    {
        $model = new LoginForm();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if($user = $model->login(Yii::$app->request->post('studio_id'))){
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

    //显示学生详情
    public function actionInfo() {
         error_reporting(0);
        $id       = Yii::$app->request->post('id');
        $admin_id = Yii::$app->request->post('admin_id');

        $user = CodeUser::getInfo($id,$admin_id);
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        return $user;

    }

    public function actionBindUser($phone_number)
    {
        $modelClass = $this->modelClass;
        $_GET['message'] = Yii::t('api', 'View Success');
        return $modelClass::findByPhoneNumber($phone_number);
    }

    //更新用户信息
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('user_id'));
        if($model){
            #$model->setScenario('api_update');
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

    //获取搜索条件 -- api
    public function actionGetSerach() {
        $modelClass = new CodeUser();
        $admin_id = Yii::$app->request->post('admin_id');
        $_GET['message'] = Yii::t('teacher','Sucessfully Get List');

        return $modelClass::getSerach($admin_id);
    }
    //获取学生列表(激活码)    
    public function actionGetList() {

        if($admin_id = Yii::$app->request->post('admin_id')) {

            $campus_id= Format::explodeValue(ChatAdmin::findOne($admin_id)->campus_id); 
        }
        
        $Teacher = ChatAdmin::findOne($admin_id);

        $visual_class =  Format::explodeValue($Teacher->class_id);

        $province  = (Yii::$app->request->post('province') != '102') ? Yii::$app->request->post('province') : NULL;  
        $is_active = (Yii::$app->request->post('is_active') != '003') ? Yii::$app->request->post('is_active') : NULL;  

        $class_id = Yii::$app->request->post('class_id');

 
        if($class_id) {
           $class_id  = (Yii::$app->request->post('class_id') != '001') ? Yii::$app->request->post('class_id') : $visual_class;
        }else{
            if($visual_class){
                $class_id = $visual_class;
            }else{
                $class_id = NULL;
            }
        }
        $studio_id = Yii::$app->request->post('studio_id');
        $test      = Yii::$app->request->post('text');
        $page      = Yii::$app->request->post('page');
        $limit     = Yii::$app->request->post('limit');

        $offset    =  $page * $limit;

        $_GET['message'] = Yii::t('teacher','Sucessfully Get List');
        $retail = CodeUser::find()
                    ->select('id, name, grade, class_id, phone_number, image, is_image, credit')
                    ->where([
                        'studio_id' => $studio_id,
                        'status' => CodeUser::STATUS_ACTIVE,
                        'class_id' => NULL,
                        'campus_id' => NULL
                    ])
                    ->andFilterWhere(['or',['like', 'name', $test],
                                           ['like', 'phone_number', $test]
                                    ])
                    ->andFilterWhere(['province' => $province])
                    ->offset($offset)
                    ->limit($limit)
                    ->indexBy('id')
                    ->asArray()
                    ->orderBy(['created_at' => SORT_DESC])
                    ->all();

        $sanhu = CodeUser::find()
        ->select('id, name, studio_id,grade, class_id, phone_number, image, is_image, credit')
        ->where(['id' => array_keys($retail)])
        ->all();
        $time = date("Y-m-d",time());
        
        if($is_active != 30){
            if($is_active){
                 $student = CodeUser::find()
                            ->joinWith('codes')
                            ->select('user.id')
                            ->where(['codes.studio_id' => $studio_id, 'user.status' => CodeUser::STATUS_ACTIVE])
                            ->andWhere(['>=','codes.due_time',$time])
                            ->andFilterWhere(['user.campus_id' => $campus_id,'user.class_id' => $class_id, 'user.province' => $province, 'codes.is_active' => $is_active])
                            ->andFilterWhere([
                                            'or',
                                            ['like', 'user.name', $test],
                                            ['like', 'user.phone_number', $test],
                                            ['like', 'codes.code', $test]
                                        ])
                            ->indexBy('id')
                            ->asArray()
                            ->orderBy(['user.created_at' => SORT_DESC])
                            ->all();
            }else{
                 $student = CodeUser::find()
                            ->joinWith('codes')
                            ->select('user.id')
                            ->where(['codes.studio_id' => $studio_id, 'user.status' => CodeUser::STATUS_ACTIVE])
                            ->andFilterWhere(['user.campus_id' => $campus_id,'user.class_id' => $class_id, 'user.province' => $province, 'codes.is_active' => $is_active])
                            ->andFilterWhere([
                                            'or',
                                            ['like', 'user.name', $test],
                                            ['like', 'user.phone_number', $test],
                                            ['like', 'codes.code', $test]
                                        ])

                            ->indexBy('id')
                            ->asArray()
                            ->orderBy(['user.created_at' => SORT_DESC])
                            ->all();                
            }
        }else{
             $student = CodeUser::find()
                        ->joinWith('codes')
                        ->select('user.id')
                        ->where(['codes.studio_id' => $studio_id, 'user.status' => CodeUser::STATUS_ACTIVE])
                        ->andFilterWhere(['user.campus_id' => $campus_id,'user.class_id' => $class_id, 'user.province' => $province])
                        ->andWhere(['<','codes.due_time',$time])
                        ->andFilterWhere([
                                        'or',
                                        ['like', 'user.name', $test],
                                        ['like', 'user.phone_number', $test],
                                        ['like', 'codes.code', $test]
                                    ])
                        ->indexBy('id')
                        ->asArray()
                        ->orderBy(['user.created_at' => SORT_DESC])
                        ->all();   
        }

        $students = CodeUser::find()
        ->joinWith('codes')
        ->select('user.id, user.name, user.studio_id,user.grade, user.class_id, user.phone_number, user.image, is_image, credit')
        ->where(['user.id' => array_keys($student)])
        ->orderBy(['user.created_at' => SORT_DESC])
        ->all();
        
        if($visual_class){
            $is_active = '007';
        }

        if(!$class_id){
            if($is_active == '004'){
               return array_slice($sanhu, $offset, $limit);
            }elseif($is_active == NULL || $is_active == '007'){
               return array_slice(array_merge($students, $sanhu), $offset, $limit);
            }else{
               return array_slice($students, $offset, $limit);
            }
        }else{
              return array_slice($students, $offset, $limit);
        }
    }

    //删除学生 -- 福文
    public function actionDelete() {

        $modelClass = $this->modelClass;

        $code_id     = Yii::$app->request->post('code_id');

        $admin_id    = Yii::$app->request->post('admin_id'); 

        $student_id  = Yii::$app->request->post('student_id'); 
        
        if(!$admin_id) {
            return SendMessage::sendErrorMsg(Yii::t('api', 'User Not Exist')); 
        }

        $role     = Yii::$app->authManager->getRolesByUser($admin_id);
        $role_key =  key($role);

        if(substr($role_key,-3) != '001') {
            return SendMessage::sendErrorMsg("没有权限!"); 
        }

        $model = $modelClass::findOne($student_id);

        if($code_id){
            $model->name     = NULL;
            $model->image    = NULL;
            $model->is_image = NULL;
            $model->phone_number = NULL;
            $model->token_value = NULL;
        }else{
            $model->status = 0;
        }

        //获取家长
        $family = Family::findOne(['relation_id'=>$student_id,'status'=>10]);

        $connect = Yii::$app->db->beginTransaction();

        if($code_id) {
            $code = ActivationCode::findById($code_id);
            if($code){
                $code->is_active = ActivationCode::USE_ACTIVE;
                #$code->status    = ActivationCode::STATUS_DELETED;
            }
        }
        try{
            if(!$model->save(false)) {
                throw new ErrorException(Errors::getInfo($model->getErrors())); 
            }

            if($family) {
                $family->relation_id = NULL;
                $family->token_value = NULL;
                if(!$family->save()){
                    throw new ErrorException(Errors::getInfo($family->getErrors()));   
                }
            }

            if($code) {
                if(!$code->save()) {
                    throw new ErrorException(Errors::getInfo($teacher->getErrors()));   
                }
            }
            $connect->commit();
            return SendMessage::sendSuccessMsg("删除成功！");
        } catch (ErrorException $e) {
            $connect->rollBack();
            return SendMessage::sendErrorMsg($e->getMessage());
        }  

    }

    //修改用户校区班级信息
    public function actionUpdateCampus() {

        $model = $this->findModel(Yii::$app->request->post('id'));
        if($model){
            $model->load(Yii::$app->getRequest()->getBodyParams(), '');
            if ($model->save(false)) {
                return SendMessage::sendSuccessMsg(Yii::t('api', 'Update Success'));
            } else {
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
?>