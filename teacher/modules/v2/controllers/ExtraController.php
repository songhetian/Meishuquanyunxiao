<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Format;
use teacher\modules\v2\models\CodeAdmin;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\NewActivationCode;
/**
  *[附加功能 新功能]
  *
  *
*/


class ExtraController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\ActivationCode';

    /**
    *[修改到期时间字段]
    *due_time
    */
    public function actionIndex()
    {
        ini_set ('memory_limit', '2048M');
        $list = NewActivationCode::find()->where(['status'=>10])->all();

        $error = array();

        $connection = Yii::$app->db->beginTransaction();

        try{
            foreach ($list as $key => $value) {
                if($value->type == 1 && $value->activetime == 0.000) {
                    $activetime = 1.000;
                }else{
                    $activetime = $value->activetime;
                }

                $due_time = date("Y-m-d",NewActivationCode::getEndTime($value->created_at,$activetime));

                $value->due_time = $due_time;

                if(!$value->save()){
                    $error[] = $key;
                }
            }

            if($error) {
                $connection->rollBack();
                return ["status"=>400,"msg"=>"操作失败!"] ;
            }else{
                $connection->commit();
                return ["status"=>200,"msg"=>"操作成功!"] ;
            }

        }catch(\Exception $e){
            $connection->rollBack();
            return ["status"=>400,"msg"=>"操作失败!"] ;
        }
    }

    /**
     *[续费]
     *code 激活码 activetime 年限
     *
     *
     *
    */

    public function actionRenew() {

        $code       = Yii::$app->request->post('code');

        $activetime = Yii::$app->request->post('activetime');

        $admin_id   = Yii::$app->request->post('admin_id');

        $item_name =  CodeAdmin::findOne($admin_id)->auths->item_name;

        $pid = substr($item_name,-3);

        if($pid != "001") {
            return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
        }

        $activetime = isset($activetime) ? $activetime : 0;

        $CodeInfo = NewActivationCode::findOne(['code'=>$code,'status'=>10]);


        if($CodeInfo->studio_id != NewActivationCode::findOne(['type'=>1,'relation_id'=>$admin_id])->studio_id) {

            return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth')); 
        }

        if(!$CodeInfo) {
            return SendMessage::sendErrorMsg("激活码不存在");
        }

        if(!in_array($CodeInfo->studio_id,Yii::$app->params['Studio'])) {
            //获取剩余数量
            if(!NewActivationCode::getCodeNum($CodeInfo->type,$activetime,$CodeInfo->studio_id)) {
                return SendMessage::sendErrorMsg("激活码数量不足");
            }
        }

        if($activetime != $CodeInfo->activetime) {

             $CodeInfo->activetime       = $activetime;
             $CodeInfo->activation_times = 1;
        }else{
             $CodeInfo->activation_times += 1;
        }

        $CodeInfo->is_active = 20;
        
        if($activetime == 0) {
            $activetime = 1;
        }

        if($CodeInfo->activetime == 0.25) {
            if(in_array($CodeInfo->studio_id,Yii::$app->params['Studio'])){
               $CodeInfo->due_time = NewActivationCode::UpdateEndTime(time(),$activetime);
            }else{
                if(in_array($CodeInfo->studio_id,Yii::$app->params['FreeThreeMonth'])) {
                    $CodeInfo->due_time = "2021-06-11";
                }else{
                    $CodeInfo->due_time = "2021-05-01";
                }
            }
        }else{
           $CodeInfo->due_time = NewActivationCode::UpdateEndTime(time(),$activetime);
        }
        

        if($CodeInfo->save()) {

            return SendMessage::sendSuccessMsg("续费成功");
        }else{
           return SendMessage::sendErrorMsg("续费失败");
        }

       
    }


}