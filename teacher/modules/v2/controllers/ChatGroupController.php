<?php
namespace teacher\modules\v2\controllers;

use Yii;
use components\Code;
use components\CreateImage;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\Tencat;
use teacher\modules\v2\models\ChatGroup;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;

class ChatGroupController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\ChatGroup';


    //口令建群
    public function actionCreate()
    {   
        $modelClass = $this->modelClass;

        $ChatGroup = new ChatGroup();

        if($modelClass::findOne(['group_id'=>Yii::$app->request->post('group_id'),'status'=>10])) {

             return SendMessage::sendErrorMsg("该群组已创建");
        }

        $studio_id = ActivationCode::findOne(['relation_id'=>$this->user_id,'type'=>1])->studio_id;

        $ChatGroup->load(Yii::$app->getRequest()->getBodyParams(),'');
        
        $ChatGroup->studio_id = $studio_id;

        $code = new Code;

        $ChatGroup->command   = $code->CreateGroup();

        if($ChatGroup->save()){
            return SendMessage::sendSuccessMsg("口令创建成功");
            // $status = Tencat::SetCommon(Yii::$app->request->post('group_id'),array(array('Key'=>'YunXiaoData','Value'=>$ChatGroup->command)));
            // if($status['ErrorCode'] == 0){
            //     return SendMessage::sendSuccessMsg("创建成功");
            // }else{
            //     return SendMessage::sendErrorMsg("创建群失败");
            // }
        }else{
            return SendMessage::sendErrorMsg("口令创建失败");
        }

    }
    //获取口令
    public function actionGetInfo($group_id) {

        $modelClass = $this->modelClass;

        $_GET['message'] = '获取成功';
        return $info = $modelClass::findOne(['group_id'=>$group_id]);

    }


    public function actionTest($group_id) {

        return Tencat::SetCommon($group_id,$app_define_list)['ActionStatus'];
    }
    //群加入成员 @TGS#1GCAM3JFY teacher2461
    public function actionAddUser($command,$account) {

        $group_id = ChatGroup::findOne(['command'=>$command,'status'=>10])->group_id;

        if(!$group_id) {
             return SendMessage::sendSuccessMsg("口令错误");  
        }

        $result =  Tencat::AddUsers($group_id,$account);

        switch ($result['MemberList'][0]['Result']) {
            case 0:
                return SendMessage::sendErrorMsg("申请失败");
                break;
            case 1:
                return SendMessage::sendSuccessMsg("申请成功");
                break;
            case 2:
                return SendMessage::sendErrorMsg("该成员已经是群成员");
                break;
            default:
                # code...
                break;
        }
    }

    //设置群头像
    public function actionSetImage($group_id) {
        $users   = array();
        $pic_list = array();
        $list =  Tencat::getGroupInfo($group_id)['MemberList'];

        foreach ($list as $key => $value) {
           $users[] = $value['Member_Account'];
        }

        $ImageList =  Tencat::getImageList($users)['UserProfileItem'];

        foreach ($ImageList as $key => $value) {
            if(preg_match("/(jpg|gif|bmp|bnp|png|jpeg)/", strtolower($value['ProfileItem'][0]['Value']))) {
                $pic_list[] = $value['ProfileItem'][0]['Value'];
            }
        }
        $filename =preg_replace('(@|#)','',$group_id);

        if(count($pic_list)){
            $CreateImage = new CreateImage($pic_list);

            $CreateImage->create($filename);

            $image = "http://api.teacher.meishuquanyunxiao.com/image/".$filename.".jpeg";

            $res =  Tencat::setGroupImage($group_id,array('face_url'=>$image));

            if($res['ErrorCode'] == 0) {
                 return SendMessage::sendSuccessMsg("设置头像成功");
            }else{
                 return SendMessage::sendErrorMsg("设置头像失败");
            }
        }


    }









}