<?php

namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Family;
use yii\data\ActiveDataProvider;
use common\models\CcLive;
use common\models\Live;
use common\models\Studio;
use common\models\Campus;
use common\models\User;
use components\Oss;
use common\models\UserFollow;
use common\models\FollowCc;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Tencat;
use teacher\modules\v2\models\Classes;
use teacher\modules\v2\models\Rbac;
use teacher\modules\v2\models\CodeAdmin;
use components\Push;
use components\PostPush;


class CcLiveController extends MainController
{
    public $modelClass = 'app\models\CcLive';
    public function actionFromCc(){
      $file  = 'log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
      $content = $_GET['type'].'-'.$_GET['liveId'].'
      ';
      if($f  = file_put_contents($file, $content,FILE_APPEND)){// 这个函数支持版本(PHP 5) 
      }
      //直播结束接口
      if($_GET['type'] == 2){
        $live = Live::findOne(['cc_id'=>$_GET['roomId'],'live_id'=>$_GET['liveId']]);
        if(!empty($live)){
          $live->start_time = $_GET['startTime'];
          $live->end_time = $_GET['endTime'];
          $live->save();
        }
      }elseif($_GET['type'] == 103){
        $live = Live::findOne(['cc_id'=>$_GET['roomId'],'live_id'=>$_GET['liveId']]);
        if(!empty($live)){
          $live->record_id = $_GET['recordId'];
          $live->save();
        }
      }
      return [
              'result' => 'OK',
            ];
    }
    //更新、创建直播间
    public function actionPostUpdateLive(){
      if(Yii::$app->getRequest()->getIsPost() && !empty($_POST['param'])){
        $decodedJson = json_decode(urldecode($_POST['param']), true);
        //针对IPAD进行处理
        $decodedJson['user_id'] = (!empty($_POST['user_id']))?$_POST['user_id']:$decodedJson['user_id'];
        $decodedJson['studio_id'] = Campus::findOne(Admin::findOne($decodedJson['user_id'])->campus_id)->studio_id;
        $decodedJson['play_status'] = 2;
        $decodedJson['play_type'] = 'phone';
        $decodedJson['start_time'] = date("Y-m-d H:i:s",time());
        $decodedJson['end_time'] = NULL;
        //不更新以下字段
        foreach ($decodedJson as $key => $value) {
          if($key == 'cc_id' || $key == 'checkurl' || $key == 'playpass' || $key == 'create_time' || $key == 'status' || $key == 'is_recommend' || empty($value)){
            unset($decodedJson[$key]);
          }
        }
        //判断数据库是否有该教师的直播间
        $cc = CcLive::find()->select('cc_id')->where('user_id='.$decodedJson['user_id'].' AND is_bespoke != 1 AND play_type="phone"  AND status = 1 ')->one();

        if(!empty($cc)){
          //直播间已存在
          $model = CcLive::find()->where('cc_id = "'.$cc->cc_id.'"')->one();
          $res = $this->UpdateLive($decodedJson,$model);
          if($res['result']=='OK'){
            if(!empty($decodedJson['description'])){
              $decodedJson['description'] = $decodedJson['description'];
            }else{
              $decodedJson['description'] = "";
            }
            $model->load($decodedJson, '');
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '直播间信息更新成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'ccmessage' => $res,
              'message' => '直播间信息更新失败',
            ];
          }
        }else{
          //直播间未存在 urlencode('中文参数值')
          $model = new CcLive();
          $data['user_id'] = $decodedJson['user_id'];
          $data['is_sideways'] = $decodedJson['is_sideways'];
          $model->title = $decodedJson['title'];
          $model->studio_id = $decodedJson['studio_id'];
          $data['title'] = urlencode($decodedJson['title']);
          $model->description = empty($decodedJson['description'])?$decodedJson['title']:$decodedJson['description'];
          $data['desc'] = empty($decodedJson['description'])?'':urlencode($decodedJson['description']);
          $data['templatetype'] = empty($decodedJson['templatetype'])?3:$decodedJson['templatetype'];
          $res = $this->CreateLive($data,$model);
          if($res['result']=='OK'){
            $old_pic_url = $model->pic_url;
            $model->load($decodedJson, '');
            $model->cc_id = $res['room']->id;
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '直播间信息创建成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => '直播间信息创建失败',
            ];
          }
        }
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非POST请求或缺少参数',
            ];
      }
    }

    //更新、创建预告
    public function actionPostUpdateForeshow(){
      if(Yii::$app->getRequest()->getIsPost() && !empty($_POST['param'])){
        $decodedJson = json_decode(urldecode($_POST['param']), true);
        //针对IPAD进行处理
        $decodedJson['user_id'] = (!empty($_POST['user_id']))?$_POST['user_id']:$decodedJson['user_id'];
        $decodedJson['studio_id'] = Campus::findOne(Admin::findOne($decodedJson['user_id'])->campus_id)->studio_id;
        $decodedJson['play_status'] = 2;
        $decodedJson['play_type'] = 'phone';
        $decodedJson['is_bespoke'] = 1;
        $decodedJson['is_sideways'] = 0;

        if($decodedJson['pic_url'] == ""){
          unset($decodedJson['pic_url']);
        }
        //不更新以下字段
        foreach ($decodedJson as $key => $value) {
          if($key == 'cc_id' || $key == 'checkurl' || $key == 'create_time' || $key == 'status' || $key == 'is_recommend' || empty($value)){
            unset($decodedJson[$key]);
          }
        }
        //判断数据库是否有该教师的直播间
        if(!empty($_POST['cc_id'])){
          $cc = CcLive::find()->select('cc_id')->where('cc_id="'.$_POST['cc_id'].'" AND is_bespoke = 1 AND status = 1  ')->one();
          //直播间已存在
          $model = CcLive::find()->where('cc_id = "'.$cc->cc_id.'"')->one();
          $res = $this->UpdateLive($decodedJson,$model);
          if($res['result']=='OK'){
            if(!empty($decodedJson['description'])){
              $decodedJson['description'] = $decodedJson['description'];
            }else{
              $decodedJson['description'] = "";
            }
            $model->load($decodedJson, '');
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '预告信息更新成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => $res,
              'message' => '预告信息更新失败',
            ];
          }
        }else{
          //直播间未存在 urlencode('中文参数值')
          $model = new CcLive();
          $data['user_id'] = $decodedJson['user_id'];
          $data['is_sideways'] = $decodedJson['is_sideways'];
          $model->title = $decodedJson['title'];
          $model->studio_id = $decodedJson['studio_id'];
          $model->is_bespoke = 1;
          $data['title'] = urlencode($decodedJson['title']);
          $model->description = empty($decodedJson['description'])?$decodedJson['title']:$decodedJson['description'];
          $data['desc'] = empty($decodedJson['description'])?'':urlencode($decodedJson['description']);
          $model->start_time = $decodedJson['start_time'];
          $model->end_time = $decodedJson['end_time'];
          $data['templatetype'] = empty($decodedJson['templatetype'])?3:$decodedJson['templatetype'];
          $res = $this->CreateLive($data,$model);
          if($res['result']=='OK'){
            $old_pic_url = $model->pic_url;
            $model->load($decodedJson, '');
            $model->cc_id = $res['room']->id;
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);


            if($decodedJson['studio_id'] == 183){
              $this->PushLive($model->title,$model->description,$model->id,$model->cc_id,$res['room']->id);
            }
            
            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '预告信息创建成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => '预告信息创建失败',
            ];
          }
        }
      }else{
        return [
              'success' => false,
              'data' => NULL,
              'message' => 'Error 请求非POST请求或缺少参数',
            ];
      }
    }


        //更新、创建PC直播间
    public function actionPostUpdatePcLive(){
      if(Yii::$app->getRequest()->getIsPost() && !empty($_POST['param'])){
        $decodedJson = json_decode(urldecode($_POST['param']), true);
        //针对IPAD进行处理
        $decodedJson['user_id'] = (!empty($_POST['user_id']))?$_POST['user_id']:$decodedJson['user_id'];
        $decodedJson['studio_id'] = Campus::findOne(Admin::findOne($decodedJson['user_id'])->campus_id)->studio_id;
        $decodedJson['play_status'] = 2;
        $decodedJson['play_type'] = 'pc';
        $decodedJson['start_time'] = date("Y-m-d H:i:s",time());
        $decodedJson['end_time'] = NULL;
        //不更新以下字段
        foreach ($decodedJson as $key => $value) {
          if($key == 'cc_id' || $key == 'checkurl' || $key == 'playpass' || $key == 'create_time' || $key == 'status' || $key == 'is_recommend' || empty($value)){
            unset($decodedJson[$key]);
          }
        }
        //判断数据库是否有该教师的直播间
        $cc = CcLive::find()->select('cc_id')->where('course_id='.$decodedJson['course_id'].' AND is_bespoke != 1 AND play_type ="pc" AND status = 1 ')->one();

        if(!empty($cc)){
          //直播间已存在
          $model = CcLive::find()->where('cc_id = "'.$cc->cc_id.'"')->one();
          $res = $this->UpdateLive($decodedJson,$model);
          if($res['result']=='OK'){
            if(!empty($decodedJson['description'])){
              $decodedJson['description'] = $decodedJson['description'];
            }else{
              $decodedJson['description'] = "";
            }
            $model->load($decodedJson, '');
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '直播间信息更新成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => NULL,
              'message' => '直播间信息更新失败',
            ];
          }
        }else{
          //直播间未存在 urlencode('中文参数值')
          $model = new CcLive();
          $data['user_id'] = $decodedJson['user_id'];
          $data['is_sideways'] = $decodedJson['is_sideways'];
          $model->title = $decodedJson['title'];
          $model->studio_id = $decodedJson['studio_id'];
          $data['title'] = urlencode($decodedJson['title']);
          $model->description = empty($decodedJson['description'])?$decodedJson['title']:$decodedJson['description'];
          $data['desc'] = empty($decodedJson['description'])?'':urlencode($decodedJson['description']);
          $data['templatetype'] = empty($decodedJson['templatetype'])?5:$decodedJson['templatetype'];
          $res = $this->CreateLive($data,$model);
          if($res['result']=='OK'){
            $old_pic_url = $model->pic_url;
            $model->load($decodedJson, '');
            $model->cc_id = $res['room']->id;
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '直播间信息创建成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => '直播间信息创建失败',
            ];
          }
        }
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非POST请求或缺少参数',
            ];
      }
    }




    //推送

    public function PushLive($title,$desc,$id,$cc_id,$share_link){
      PostPush::PushMsg($desc,$title,'发布新预告','foreshow',$id,$share_link,0,1,1,$cc_id);
    }

    //预定预告
    public function actionFollowCc(){
      if(Yii::$app->getRequest()->getIsGet() && !empty($_GET['user_id']) && !empty($_GET['user_type']) && isset($_GET['cc_id'])){
        $fcc = new FollowCc();
        $fcc->timer = time();
        $fcc->user_id = $_GET['user_id'];
        $fcc->user_type = $_GET['user_type'];
        $fcc->cc_id = $_GET['cc_id'];
        $fcc->save();
        return [
          'success' => true,
          'data' => $fcc,
          'message' => '预定预告成功',
        ];
      }else{
        return [
            'success' => false,
            'data' => array(),
            'message' => 'Error 请求非GET请求或缺少参数',
        ];
      }
    }

      //获取直播轮播图
    public function actionGetCcBanner(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['page']) && !empty($_GET['limit']) && !empty($_GET['user_id']) && !empty($_GET['user_type'])){
        if($_GET['user_type'] == 'teacher'){
          $studio_id = Campus::findOne(Admin::findOne($_GET['user_id'])->campus_id)->studio_id;
          $class_id = Admin::findOne($_GET['user_id'])->class_id;
        }else if($_GET['user_type'] == 'student'){
          $studio_id = User::findOne($_GET['user_id'])->studio_id;
          $class_id = User::findOne($_GET['user_id'])->class_id;
        }else{
          $studio_id = Family::findOne($_GET['user_id'])->studio_id;
          $class_id = 0;
        }


        $offset = $_GET['page']*$_GET['limit'];
        $limit = $_GET['limit'];
        $list = array();


        $classstr = "(";
        if (!empty($class_id) && $class_id != 0) {

          $arr = explode(",", $class_id);
          foreach ($arr as $key => $value) {
            $classstr .= ' CONCAT(",",tosee,",") LIKE "%,'.$value.',%" or ';
          }
          #CONCAT(",",tosee,",") LIKE "%,'..',%"
        }
        $classstr .= " tosee = 1)";

        if(empty($class_id) && $_GET['user_type'] == 'teacher'){
          $classstr = " 1 = 1 ";
        }



        $livedata = Live::find()->where('end_time is null AND '.$classstr.' AND course_id is null AND status = 1 AND start_time<= "'.date("Y-m-d H:i:s",time()).'" AND studio_id='.$studio_id)->asArray()->all();
        foreach ($livedata as $key => $value) {
          $livedata = Live::findOne($value['id'])->toArray();
          $livedata['live_type'] = 'live';
          $list[] = $this->AddUserField($livedata);
        }

        $lid = '"0"';
        foreach ($list as $key => $value) {
          $lid .= ',"'.$value['cc_id'].'"';
        }

        //--------------预告
        $foreshowdata = CCLive::find()->where('status = 1 AND '.$classstr.' AND course_id is null AND (end_time >= "'.date("Y-m-d H:i:s",time()).'" OR start_time>= "'.date("Y-m-d H:i:s",time()).'") AND is_bespoke=1 AND studio_id='.$studio_id.' AND cc_id not in ('.$lid.')')->orderBy('start_time ASC')->offset($offset)->limit($limit)->asArray()->all();
        if(!empty($foreshowdata) && is_array($foreshowdata)){
          foreach ($foreshowdata as $key => $value) {

            $is_follow = FollowCc::find()->where('user_id = '.$_GET['user_id'].' AND user_type = "'.$_GET['user_type'].'" AND cc_id = "'.$value['cc_id'].'"')->one();
            if(!empty($is_follow)){
              $value['is_cc_follow'] = true;
            }else{
              $value['is_cc_follow'] = false;
            }
            $value['live_type'] = 'foreshow';
            $list[] = $this->AddUserField($value);
          }
        }
        if(!empty($list)){
          return [
                'success' => true,
                'data' => $list,
                'message' => '操作成功',
            ];
        }else{
                return [
              'success' => true ,
              'data' => array(),
              'message' => '暂无预告',
          ];
        }
      }else{
              return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
          ];
      }
    }

    //获取列表
    public function actionGetCcList(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['page']) && !empty($_GET['limit']) && !empty($_GET['user_id']) && !empty($_GET['user_type'])){
        if($_GET['user_type'] == 'teacher'){
          $studio_id = Admin::findOne($_GET['user_id'])->studio_id;
          $class_id = Admin::findOne($_GET['user_id'])->class_id;
        }else if($_GET['user_type'] == 'student'){
          $studio_id = User::findOne($_GET['user_id'])->studio_id;
          $class_id = User::findOne($_GET['user_id'])->class_id;
        }else{
          $studio_id = Family::findOne($_GET['user_id'])->studio_id;
          $class_id = 0;
        }

        $cclive_type = '';
        if(isset($_GET['cclive_type'])){
          $cclive_type = ' AND cclive_type like "'.$_GET['cclive_type'].'%"';
        }
        $offset = $_GET['page']*$_GET['limit'];
        $limit = $_GET['limit'];
        $list = array();


        $classstr = "(";
        if (!empty($class_id) && $class_id != 0) {

          $arr = explode(",", $class_id);
          foreach ($arr as $key => $value) {
            $classstr .= ' CONCAT(",",tosee,",") LIKE "%,'.$value.',%" or ';
          }
          #CONCAT(",",tosee,",") LIKE "%,'..',%"
        }
        $classstr .= " tosee = 1)";

        if(empty($class_id) && $_GET['user_type'] == 'teacher'){
          $classstr = " 1 = 1 ";
        }
        $livedata = Live::find()->where('end_time is null AND course_id is null  AND '.$classstr.' AND status = 1 AND start_time<= "'.date("Y-m-d H:i:s",time()).'"  AND studio_id='.$studio_id.' '.$cclive_type)->offset($offset)->limit($limit)->asArray()->all();
        foreach ($livedata as $key => $value) {
          $livedata = Live::findOne($value['id'])->toArray();
          $livedata['live_type'] = 'live';
          $list[] = $this->AddUserField($livedata);
        }
        $lid = '"0"';
        foreach ($list as $key => $value) {
          $lid .= ',"'.$value['cc_id'].'"';
        }

        //--------------预告
        $foreshowdata = CCLive::find()->where('status = 1 AND '.$classstr.' AND course_id is null AND (end_time >= "'.date("Y-m-d H:i:s",time()).'" OR start_time>= "'.date("Y-m-d H:i:s",time()).'") AND is_bespoke=1 AND studio_id='.$studio_id.' AND cc_id not in ('.$lid.')')->orderBy('start_time ASC')->offset($offset)->limit($limit)->asArray()->all();
        if(!empty($foreshowdata) && is_array($foreshowdata)){
          foreach ($foreshowdata as $key => $value) {

            $is_follow = FollowCc::find()->where('user_id = '.$_GET['user_id'].' AND user_type = "'.$_GET['user_type'].'" AND cc_id = "'.$value['cc_id'].'"')->one();
            if(!empty($is_follow)){
              $value['is_cc_follow'] = true;
            }else{
              $value['is_cc_follow'] = false;
            }
            $value['live_type'] = 'foreshow';
            $list[] = $this->AddUserField($value);
          }
        }
        //---------------回放
        $livedata = Live::find()->where('status = 1 AND '.$classstr.' AND course_id is null AND studio_id='.$studio_id.' '.$cclive_type)->orderBy('end_time DESC')->offset($offset)->limit($limit)->asArray()->all();
        foreach ($livedata as $key => $value) {
          if(empty($value['end_time'])){
            $data['roomid'] = $value['cc_id'];
            $data['userid'] = Yii::$app->params['cc']['userid'];
            $data['liveid'] = $value['live_id'];
            $ccback = $this->record($data);
            if($ccback['result']=='OK'){
              $live = Live::findOne($value['id']);
              $live->end_time = $ccback['records'][0]->stopTime;
              $live->record_id = $ccback['records'][0]->id;
              $live->save();
              $livedatat = Live::findOne($live->id)->toArray();
              if(!empty($livedata['end_time'])){
                $livedatat['live_type'] = 'back';
                $list[] = $this->AddUserField($livedatat);
              }
            }
          }else{
            
            $value['live_type'] = 'back';
            $list[] = $this->AddUserField($value);
          }
        }

        if(!empty($list)){
        return [
              'success' => true,
              'data' => $list,
              'message' => '操作成功',
          ];
        }else{
                  return [
              'success' => true ,
              'data' => array(),
              'message' => '暂无正在直播的老师',
          ];
        }
      }else{
                return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
          ];
      }
    }
    //删除回放
    public function actionDelCclive(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['cc_id'])  && !empty($_GET['user_type']) && !empty($_GET['user_id'])){
        if($_GET['user_type']=='teacher'){
          $studio_id = Campus::findOne(Admin::findOne($_GET['user_id'])->campus_id)->studio_id;

          if(!empty($_GET['live_id'])){
            $live = Live::findOne(['live_id'=>$_GET['live_id'],'cc_id'=>$_GET['cc_id'],'status' => 1,'studio_id'=>$studio_id]);
          }else{
            $live = CcLive::findOne(['cc_id'=>$_GET['cc_id'],'status' => 1,'studio_id'=>$studio_id]);
          }
          

          if(!empty($live)){
            if($live->user_id == $_GET['user_id']){
              $live->status = 2;
              $live->save();
              return [
                'success' => true,
                'data' => array(),
                'message' => '删除成功',
              ];
            }else{
              $item_name =  CodeAdmin::findOne($_GET['user_id'])->auths->item_name;

              $pid = substr($item_name,-3);
              if($pid != "001") {
                return [
                  'success' => false,
                  'data' => array(),
                  'message' => '暂无权限，请联系管理员！',
                ];
              }else{
                $live->status = 2;
                $live->save();
                return [
                  'success' => true,
                  'data' => array(),
                  'message' => '删除成功',
                ];
              }
            }
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => '回放已不存在',
            ];
          }
        }else{
          return [
              'success' => false,
              'data' => array(),
              'message' => '暂无权限，请联系管理员！',
            ];
        }
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
            ];
      }
    }

    //获取直播信息
    public function actionGetCcInfo(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['cc_id']) && !empty($_GET['live_id'])){
        $live = Live::find()->where('live_id="'.$_GET['live_id'].'" AND cc_id="'.$_GET['cc_id'].'" AND status = 1')->one();
        if(!empty($live) && $live->end_time != NULL){
          $live->connections = $live->connections+1;
          $live->save();
          $live = $live->toArray();
          $livedata = $this->AddUserField($live);
          return [
              'success' => true,
              'data' => $livedata,
              'message' => '操作成功',
            ];
        }else{
          $data['roomid'] = $_GET['cc_id'];
          $data['userid'] = Yii::$app->params['cc']['userid'];
          $data['liveid'] = $_GET['live_id'];
          $ccback = $this->record($data);
          if($ccback['result']=='OK' && empty($live->end_time)){
            $live->end_time = $ccback['records'][0]->stopTime;
            $live->record_id = $ccback['records'][0]->id;
            $live->save();

            $livedata = Live::findOne($live->id)->toArray();

            if(empty($livedatat['end_time'])){
              $livedatat['live_type'] = 'live';
            }else{
              $livedatat['live_type'] = 'back';
            }
            $livedata = $this->AddUserField($livedata);
            return [
              'success' => true,
              'data' => $livedata,
              'message' => '操作成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => 'Error liveId有误',
            ];
          }
        }
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
            ];
      }
    }
    //获取回放地址
    public function actionGetCcUrl(){
      if(Yii::$app->getRequest()->getIsGet() && !empty($_GET['record_id'])){
        $data['userid'] = Yii::$app->params['cc']['userid'];
        $data['recordid'] = $_GET['record_id'];
        $ccback = $this->recordsearch($data);
        return [
            'success' => true,
            'data' => $ccback,
            'message' => '操作成功',
          ];
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
            ];
      }
    }



    //获取预告信息
    public function actionGetForeshowInfo(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['cc_id'])){
        $live = CcLive::find()->where(' cc_id="'.$_GET['cc_id'].'" AND status = 1')->one();

        if(!empty($live)){
          $live = $live->toArray();
          $livedata = $this->AddUserField($live);

          $livedata['live_type'] = "foreshow";
          return [
              'success' => true,
              'data' => $livedata,
              'message' => '操作成功',
            ];
        }
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
            ];
      }
    }

    public function actionCeShi(){
      Tencat::SendMsg(array('teacher2068'),'开播了');
    }








    //定时获取直播信息
    public function actionTimeGetCc(){
      echo "
      GO!";
      
      //实时获取
      $ccback = $this->broadcasting(array('userid'=>Yii::$app->params['cc']['userid']));
      echo "CCBACK:";
      var_dump($ccback);
      $ids = array();
      $ids[] = '"ABC"';
      if($ccback['result']=='OK'){
        foreach ($ccback['rooms'] as $key => $value) {
          $ids[] = '"'.$value->roomId.'"';
          echo "CCID:".$value->roomId;
          $ccroom = CcLive::find()->where('cc_id = "'.$value->roomId.'" AND status = 1')->asArray()->all();
          if(!empty($ccroom)){
            $livedata = Live::find()->where('cc_id = "'.$value->roomId.'" AND live_id = "'.$value->liveId.'" AND status = 1 ')->asArray()->all();
            if(empty($livedata)){

              $livet = new Live();
              $livet->cc_id = $value->roomId;
              $livet->live_id = $value->liveId;
              $livet->start_time = $value->startTime;
              $livet->play_type = $ccroom[0]['play_type'];
              $livet->user_id = $ccroom[0]['user_id'];
              $livet->pic_url = $ccroom[0]['pic_url'];
              $livet->course_id = $ccroom[0]['course_id'];
              $livet->description = $ccroom[0]['description'];
              $livet->is_sideways = $ccroom[0]['is_sideways'];
              $livet->title = $ccroom[0]['title'];
              $livet->tosee = $ccroom[0]['tosee'];
              $livet->studio_id = $ccroom[0]['studio_id'];
              $livet->cclive_type = $ccroom[0]['cclive_type'];
              $livet->save();

              echo ' 
              SAVE:'.$value->liveId;
            }elseif(empty($livedata['end_time'])){
              $live = Live::find()->where('live_id="'.$livedata['live_id'].'" AND cc_id="'.$livedata['cc_id'].'" AND status = 1')->one();
              $data['roomid'] = $value->roomId;
              $data['userid'] = Yii::$app->params['cc']['userid'];
              $data['liveid'] = $value->liveId;
              $cclback = $this->record($data);
              if($cclback['result']=='OK' && $cclback['records'][0]->stopTime != ""){

                $live->end_time = $cclback['records'][0]->stopTime;
                $live->record_id = $cclback['records'][0]->id;
                $live->save();
              }
            }else{
              $live = Live::find()->where('live_id="'.$livedata['live_id'].'" AND cc_id="'.$livedata['cc_id'].'" AND status = 1')->one();
              $live->end_time = null;
              $live->save();
            }
          }
        }
        // $endlive = Live::find()->where('cc_id not in ('.implode(",", $ids).') AND status = 1 AND end_time is null ')->asArray()->all();
        // var_dump($endlive);
        // if(!empty($endlive)){
        //   foreach ($endlive as $key => $value) {
        //     $live = Live::find()->where('live_id="'.$value['live_id'].'" AND cc_id="'.$value['cc_id'].'" AND status = 1')->one();
        //     $data['roomid'] = $live->cc_id;
        //     $data['userid'] = Yii::$app->params['cc']['userid'];
        //     $data['liveid'] = $live->live_id;
        //     $cclback = $this->record($data);
        //     echo 'end';
        //     echo $cclback['result'];
        //     echo "
        //     ";
        //     if($cclback['result']=='OK'){
        //       $live->end_time = $cclback['records'][count($cclback['records'])-1]->stopTime;
        //       $live->record_id = $cclback['records'][count($cclback['records'])-1]->id;
        //       $live->save();
        //     }
        //   }
        // }
      } 

      echo '
        ';
    }
    //获取直播间连接数
    public function ConnectionsLive($data){
      //进行加密
      $ccstr = $this->pwdmd5($data,'connections');

      //获取接口返回信息并处理
      str_replace("&amp;", "&", $ccstr);
      $res = @file_get_contents($ccstr);
      $res = json_decode($res);
      $res = (array)$res;
      return $res;
    }

    //获取直播人数
    public function actionGetCcPpNum(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['cc_id'])){
        $time = time();
        if(!empty($_GET['live_id'])){
          $live = Live::find()->where('live_id="'.$_GET['live_id'].'" AND cc_id="'.$_GET['cc_id'].'" AND status = 1')->one();
          $live = $live->toArray();
          if(!empty($live['end_time'])){
            $time =strtotime($live['end_time']);
          }
          return [
              'success' => true,
              'data' => array('connections'=>$live['connections'],'longtime' => date('H时i分s秒',($time-strtotime($live['start_time']))-(8*60*60))),
              'message' => '操作成功',
            ];
        }else{
          $live = Live::find()->where(' cc_id="'.$_GET['cc_id'].'" AND status = 1')->orderBy('start_time DESC')->one();
          $live = $live->toArray();
          if(!empty($live['end_time'])){
            $time = strtotime($live['end_time']);
          }
          return [
              'success' => true,
              'data' => array('connections'=>$live['connections'],'longtime' => date('H时i分s秒',($time-strtotime($live['start_time']))-(8*60*60))),
              'message' => '操作成功',
            ];
        }


                return [
            'success' => true,
            'data' => array('connections'=>0,'longtime' => '00时00分00秒'),
            'message' => '操作成功',
          ];
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
            ];
      }
    }
    //获取回放信息
    public function record($data){
      //进行加密
      $ccstr = $this->pwdmd5($data,'record');

      //获取接口返回信息并处理
      str_replace("&amp;", "&", $ccstr);
      $res = file_get_contents($ccstr);
      $res = json_decode($res);
      $res = (array)$res;
      return $res;
    }


    //获取回放信息
    public function recordsearch($data){
      //进行加密
      $ccstr = $this->pwdmd5($data,'recordsearch');

      //获取接口返回信息并处理
      str_replace("&amp;", "&", $ccstr);
      $res = file_get_contents($ccstr);
      $res = json_decode($res);
      $res = (array)$res;
      return $res;
    }

    //获取当前在线直播间
    public function broadcasting($data){
      //进行加密
      $ccstr = $this->pwdmd5($data,'broadcasting');

      //获取接口返回信息并处理

      str_replace("&amp;", "&", $ccstr);
      $res = file_get_contents($ccstr);
      $res = json_decode($res);
      $res = (array)$res;
      return $res;
    }
    //创建直播间
    public function CreateLive($data,&$model){
      $urlarr = array();
      //用户ID
      $model->user_id = $data['user_id'];
      //直播间基本信息
      $model->is_sideways = $data['is_sideways'];
      $urlarr['name'] = $data['title'];
      $urlarr['desc'] = $data['desc'];
      //默认信息
      $urlarr['userid'] = Yii::$app->params['cc']['userid'];
      $urlarr['templatetype'] = $model->templatetype = empty($data['templatetype'])?($data['play_type']=="phone"?3:5):$data['templatetype'];
      $urlarr['publisherpass'] = $model->publisherpass = '123456';
      $urlarr['assistantpass'] = $model->assistantpass = 'Meishuquan2015';
      $urlarr['authtype'] = $model->authtype = 2;
      //$urlarr['playpass'] = $model->playpass = '';
      //$urlarr['checkurl'] = $model->checkurl = '';
      $urlarr['barrage'] = $model->barrage = 1;
      $urlarr['foreignpublish'] = $model->foreignpublish = 0;
      $urlarr['openlowdelaymode'] = $model->openlowdelaymode = 1;
      $urlarr['showusercount'] = $model->showusercount = 1;
      //进行加密
      $ccstr = $this->pwdmd5($urlarr,'createlive');
      //获取接口返回信息并处理
      str_replace("&amp;", "&", $ccstr);
      $res = file_get_contents($ccstr);
      $res = json_decode($res);
      $res = (array)$res;
      return $res;
    }

    //更新直播间
    public function UpdateLive($decodedJson,&$model){
      $model->is_sideways = $decodedJson['is_sideways'];
      $urlarr = array();
      //处理标题详情
      if(!empty($decodedJson['title'])){
        $urlarr['name'] = urlencode($decodedJson['title']);
      }
      if(!empty($decodedJson['description'])){
        $urlarr['desc'] = urlencode($decodedJson['description']);
      }else{
        $urlarr['desc'] = "";

      }
      //默认信息
      $urlarr['userid'] = Yii::$app->params['cc']['userid'];

      $urlarr['templatetype'] = $model->templatetype = empty($decodedJson['templatetype'])?($decodedJson['play_type']=="phone"?3:5):$decodedJson['templatetype'];
      $urlarr['authtype'] = $model->authtype = 2;
      $urlarr['publisherpass'] = empty($decodedJson['publisherpass'])?'123456':$decodedJson['publisherpass'];
      $urlarr['assistantpass'] = empty($decodedJson['assistantpass'])?'Meishuquan2015':$decodedJson['assistantpass'];
      $urlarr['barrage'] = empty($decodedJson['barrage'])?1:$decodedJson['barrage'];
      $urlarr['foreignpublish'] = empty($decodedJson['foreignpublish'])?0:$decodedJson['foreignpublish'];
      $urlarr['openlowdelaymode'] = empty($decodedJson['openlowdelaymode'])?1:$decodedJson['openlowdelaymode'];
      $urlarr['showusercount'] = empty($decodedJson['showusercount'])?1:$decodedJson['showusercount'];
      $urlarr['roomid'] = $model->cc_id;
      //进行加密
      $ccstr = $this->pwdmd5($urlarr,'updatelive');
      //获取接口返回信息并处理

      str_replace("&amp;", "&", $ccstr);
      $res = file_get_contents($ccstr);
      $res = json_decode($res);
      $res = (array)$res;
      return $res;
    }

    //获取用户直播间信息
    public function actionGetUserLive(){
      if(Yii::$app->getRequest()->getIsGet() && !empty($_GET['cc_id'])){
        $cc = CcLive::find()->where(['cc_id'=>$_GET['cc_id']])->one()->toArray();
        if(!empty($cc)){
          $cc['is_follow'] = 1;
          if(!empty($_GET['user_id'])){
            $query = UserFollow::find();
            $query->where(['status'=>1,'user_id'=>$_GET['user_id'],'follow_user_id'=>$cc['user_id']])->one();
            if(!empty($query)){
              $cc['is_follow'] = 2;
            }
          }
          $live = Live::find()->where(['cc_id'=>$cc['cc_id']])->orderBy('start_time DESC')->one();
          if(!empty($live)){
            $cc['play_status'] = $live->play_status;
            $cc['start_time'] = $live->start_time;
            $cc['end_time'] = $live->end_time;
            $cc['title'] = $live->title;
            $cc['live_id'] = $live->live_id;
          }
          if($live['play_status']==2){
            $cc['type'] = 1;
          }else{
            $cc['type'] = 3;
          }
          $cc = $this->AddUserField($cc);
          return [
              'success' => true,
              'data' => $cc,
              'message' => '获取直播间信息成功',
            ];
        }else{
          return [
              'success' => true,
              'data' => $cc,
              'message' => '当前用户没有直播间',
            ];
        }
      }
    }

    //定时获取直播间信息
    public function actionGetTimeLiveCon(){
      if(Yii::$app->getRequest()->getIsGet() && !empty($_GET['cc_id'])){
        $cc = CcLive::find()->where(['cc_id'=>$_GET['cc_id']])->orderBy('start_time DESC')->one()->toArray();

            $data['roomid'] = $_GET['cc_id'];
            $data['userid'] = Yii::$app->params['cc']['userid'];
            $data['liveid'] = $_GET['live_id'];
            $ccback = $this->ConnectionsLive($data);

            var_dump($ccback);exit;
        if(empty($cc['connections'])){
          $datajson['connections'] = 0;
          $datajson['longtime'] = date('H时i分s秒',(time()-strtotime($cc['start_time']))-(8*60*60));
        }
        return [
              'success' => true,
              'data' => $datajson,
              'message' => '获取直播间信息成功',
            ];
      }
    }

    //排序规则
    public function my_sort($arrays,$sort_key,$sort_key2){
      if(is_array($arrays)){
        foreach ($arrays as $array){
          if(is_array($array)){
            $key_arrays[] = $array[$sort_key];   
          }else{
            return false;   
          }
        }
      }else{
          return false;   
      }

      if(is_array($arrays)){   
        foreach ($arrays as $array){   
          if(is_array($array)){   
            $key_arrays2[] = $array[$sort_key2];   
          }else{   
            return false;   
          }
        }   
      }else{   
        return false;   
      } 
      array_multisort($key_arrays,SORT_DESC,SORT_NUMERIC,$key_arrays2,SORT_DESC,SORT_NUMERIC,$arrays);   
      return $arrays;   
    }

    //CC加密规则
    public function pwdmd5($arr,$type){
        $time = time();
        $ccstr = "";
        if(is_array($arr)){
          ksort($arr);
          foreach ($arr as $key => $value){
            $ccstr .= $key.'='.$value.'&';
          }
        }
        $ccstr .= 'time='.$time;
        $ccstr .= '&hash='.md5($ccstr.'&salt='.Yii::$app->params['cc']['apikey']);
        $ccstr = Yii::$app->params['cc'][$type].'?'.$ccstr;
        
        return $ccstr;
    }
    //获取直播分类
    public function actionGetCcType(){
      return array(
        'success' => true,
        'data' => array(
        array(
          'name'=>'未分类',
          'image' => 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/classify%2F4.jpeg?x-oss-process=style/320x320',
          'cclive_type'=>0
        ),
          array(
            'name'=>'素描',
            'image' => 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/classify%2F0.jpeg?x-oss-process=style/320x320',
            'cclive_type'=>1,
            'category_id' => 1,
            'type_v2' => array(
              array(
                'name' => '素描头像',
                'cclive_type' => 11,
                'category_id' => 11,
              ),
              array(
                'name' => '素描静物',
                'cclive_type' => 12,
                'category_id' => 12,
              ),
              array(
                'name' => '半身像',
                'cclive_type' => 13,
                'category_id' => 13,
              ),
              array(
                'name' => '石膏像',
                'cclive_type' => 14,
                'category_id' => 14,
              ),
              array(
                'name' => '几何形体',
                'cclive_type' => 15,
                'category_id' => 15,
              )
            )
          ),
          array(
            'name'=>'色彩',
            'image' => 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/classify%2F1.jpeg?x-oss-process=style/320x320',
            'cclive_type'=>2,
                'category_id' => 2,
            'type_v2' => array(
              array(
                'name' => '色彩静物',
                'cclive_type' => 21,
                'category_id' => 21,
              ),
              array(
                'name' => '色彩头像',
                'cclive_type' => 22,
                'category_id' => 22,
              ),
              array(
                'name' => '色彩场景',
                'cclive_type' => 23,
                'category_id' => 23,
              ),
            )
          ),
          array(
            'name'=>'速写',
            'image' => 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/classify%2F2.jpeg?x-oss-process=style/320x320',
            'cclive_type'=>3,
            'category_id' => 3,
            'type_v2' => array(
              array(
                'name' => '单人速写',
                'cclive_type' => 31,
                'category_id' => 31,
              ),
              array(
                'name' => '人物组合',
                'cclive_type' => 32,
                'category_id' => 32,
              ),
              array(
                'name' => '命题速写',
                'cclive_type' => 33,
                'category_id' => 33,
              ),
              array(
                'name' => '速写场景',
                'cclive_type' => 34,
                'category_id' => 34,
              )
            )
          ),
          array(
            'name'=>'设计',
            'image' => 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/classify%2F3.jpeg?x-oss-process=style/320x320',
            'cclive_type'=>4,
            'category_id' => 4,
            'type_v2' => array(
              array(
                'name' => '设计素描',
                'cclive_type' => 41,
                'category_id' => 41,
              ),
              array(
                'name' => '设计色彩',
                'cclive_type' => 42,
                'category_id' => 42,
              ),
              array(
                'name' => '创意速写',
                'cclive_type' => 43,
                'category_id' => 43,
              ),
              array(
                'name' => '设计基础',
                'cclive_type' => 44,
                'category_id' => 44,
              ),
              array(
                'name' => '创意设计',
                'cclive_type' => 45,
                'category_id' => 45,
              )
            )
          ),
          array(
            'name' => '专题',
            'image' => 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/classify%2F4.jpeg?x-oss-process=style/320x320',
            'cclive_type' => 5,
                'category_id' => 5,
            'type_v2' => array(
              array(
                'name' => '讲大课',
                'cclive_type' => 51,
                'category_id' => 51,
              ),
              array(
                'name' => '文化课',
                'cclive_type' => 52,
                'category_id' => 52,
              ),
              array(
                'name' => '其他',
                'cclive_type' => 53,
                'category_id' => 53,
              ),
            )
          )
        ),
        "error"=>0,
        "message"=>"获取列表成功",
      );
    }
    //获取可见范围
    public function actionGetToSee($user_id){

        $studio_id = Campus::findOne(Admin::findOne($user_id)->campus_id)->studio_id;

        $campus_id =  array_column(Campus::find()->select('id')->where("studio_id = ".$studio_id)->asArray()->all(), 'id') ;

        $class = Classes::find()->where(['campus_id'=>$campus_id,'status'=>10])->asArray()->all();
        
        $data = array(
          array('title'=>'全部可见','id'=>1),
          array('title'=>'私人','id'=>0),
        );

        foreach ($class as $key => $value) {
          $data[] = array('title'=>$value['name'],'id'=>$value['id']);
        }
        return [
                'success' => true,
                'data' => $data,
                'message' => '获取直播间信息成功',
              ];
    }


/*****************   课件相关  *******************************/
    //更新、创建直播间
    public function actionPostUpdateCourseLive(){
      if(Yii::$app->getRequest()->getIsPost() && !empty($_POST['param'])){
        $decodedJson = json_decode(urldecode($_POST['param']), true);
        //针对IPAD进行处理
        $decodedJson['user_id'] = (!empty($_POST['user_id']))?$_POST['user_id']:$decodedJson['user_id'];
        $decodedJson['studio_id'] = Campus::findOne(Admin::findOne($decodedJson['user_id'])->campus_id)->studio_id;
        $decodedJson['play_status'] = 2;
        $decodedJson['play_type'] = empty($decodedJson['play_type'])?"phone":$decodedJson['play_type'];
        $decodedJson['start_time'] = date("Y-m-d H:i:s",time());
        $decodedJson['end_time'] = NULL;
        //不更新以下字段
        foreach ($decodedJson as $key => $value) {
          if($key == 'cc_id' || $key == 'checkurl' || $key == 'playpass' || $key == 'create_time' || $key == 'status' || $key == 'is_recommend' || empty($value)){
            unset($decodedJson[$key]);
          }
        }
        //判断数据库是否有该教师的直播间
        $cc = CcLive::find()->select('cc_id')->where('user_id='.$decodedJson['user_id'].' AND course_id ='.$decodedJson['course_id'].'    AND is_bespoke != 1 ')->one();

        if(!empty($cc)){
          //直播间已存在
          $model = CcLive::find()->where('cc_id = "'.$cc->cc_id.'"')->one();
          $res = $this->UpdateLive($decodedJson,$model);
          if($res['result']=='OK'){
            if(!empty($decodedJson['description'])){
              $decodedJson['description'] = $decodedJson['description'];
            }else{
              $decodedJson['description'] = "";
            }
            $model->load($decodedJson, '');
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '直播间信息更新成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => '直播间信息更新失败',
            ];
          }
        }else{
          //直播间未存在 urlencode('中文参数值')
          $model = new CcLive();
          $data['user_id'] = $decodedJson['user_id'];
          $data['is_sideways'] = 1;
          $model->title = $decodedJson['title'];
          $model->studio_id = $decodedJson['studio_id'];
          $data['title'] = urlencode($decodedJson['title']);
          $model->description = empty($decodedJson['description'])?$decodedJson['title']:$decodedJson['description'];
          $data['desc'] = empty($decodedJson['description'])?'':urlencode($decodedJson['description']);
          $data['templatetype'] = empty($decodedJson['templatetype'])?($decodedJson['play_type'] == 'phone'?3:5):$decodedJson['templatetype'];
          $data['play_type'] = empty($decodedJson['play_type'])?"phone":$decodedJson['play_type'];


          $res = $this->CreateLive($data,$model);
          if($res['result']=='OK'){
            $old_pic_url = $model->pic_url;
            $model->load($decodedJson, '');
            $model->cc_id = $res['room']->id;
            $model->course_id = $decodedJson['course_id'];
            $model->save();
            $ccdate = CcLive::findOne($model->id)->toArray();
            $ccdate = $this->AddUserField($ccdate);

            return [
              'success' => true,
              'data' => $ccdate,
              'message' => '直播间信息创建成功',
            ];
          }else{
            return [
              'success' => false,
              'data' => array(),
              'message' => '直播间信息创建失败',
            ];
          }
        }
      }else{
        return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非POST请求或缺少参数',
            ];
      }
    }



 //获取课件id直播列表
    public function actionGetCourseCcList(){
      if(Yii::$app->getRequest()->getIsGet() && isset($_GET['page']) && !empty($_GET['limit']) && !empty($_GET['user_id']) && !empty($_GET['user_type']) && !empty($_GET['course_id']) ){
        if($_GET['user_type'] == 'teacher'){
          $studio_id = Admin::findOne($_GET['user_id'])->studio_id;
          $class_id = Admin::findOne($_GET['user_id'])->class_id;
        }else if($_GET['user_type'] == 'student'){
          $studio_id = User::findOne($_GET['user_id'])->studio_id;
          $class_id = User::findOne($_GET['user_id'])->class_id;
        }else{
          $studio_id = Family::findOne($_GET['user_id'])->studio_id;
          $class_id = 0;
        }

        $cclive_type = '';
        if(isset($_GET['cclive_type'])){
          $cclive_type = ' AND cclive_type like "'.$_GET['cclive_type'].'%"';
        }
        $offset = $_GET['page']*$_GET['limit'];
        $limit = $_GET['limit'];
        $list = array();


        $classstr = "(";
        if (!empty($class_id) && $class_id != 0) {

          $arr = explode(",", $class_id);
          foreach ($arr as $key => $value) {
            $classstr .= ' CONCAT(",",tosee,",") LIKE "%,'.$value.',%" or ';
          }
          #CONCAT(",",tosee,",") LIKE "%,'..',%"
        }
        $classstr .= " tosee = 1)";

        if(empty($class_id) && $_GET['user_type'] == 'teacher'){
          $classstr = " 1 = 1 ";
        }
        $livedata = Live::find()->where('end_time is null AND '.$classstr.' AND status = 1 AND start_time<= "'.date("Y-m-d H:i:s",time()).'"  AND course_id='.$_GET['course_id'].' AND studio_id='.$studio_id.' '.$cclive_type)->offset($offset)->limit($limit)->asArray()->all();
        foreach ($livedata as $key => $value) {
          $livedata = Live::findOne($value['id'])->toArray();
          $livedata['live_type'] = 'live';
          $list[] = $this->AddUserField($livedata);
        }
        $lid = '"0"';
        foreach ($list as $key => $value) {
          $lid .= ',"'.$value['cc_id'].'"';
        }
        //---------------回放
        $livedata = Live::find()->where('status = 1  AND course_id='.$_GET['course_id'].' AND '.$classstr.' AND studio_id='.$studio_id.' '.$cclive_type)->orderBy('end_time DESC')->offset($offset)->limit($limit)->asArray()->all();
        foreach ($livedata as $key => $value) {
          if(empty($value['end_time'])){
            $data['roomid'] = $value['cc_id'];
            $data['userid'] = Yii::$app->params['cc']['userid'];
            $data['liveid'] = $value['live_id'];
            $ccback = $this->record($data);
            if($ccback['result']=='OK'){
              $live = Live::findOne($value['id']);
              $live->end_time = $ccback['records'][0]->stopTime;
              $live->record_id = $ccback['records'][0]->id;
              $live->save();
              $livedatat = Live::findOne($live->id)->toArray();
              if(!empty($livedata['end_time'])){
                $livedatat['live_type'] = 'back';
                $list[] = $this->AddUserField($livedatat);
              }
            }
          }else{
            
            $value['live_type'] = 'back';
            $list[] = $this->AddUserField($value);
          }
        }

        if(!empty($list)){
        return [
              'success' => true,
              'data' => $list,
              'message' => '操作成功',
          ];
        }else{
                  return [
              'success' => true ,
              'data' => array(),
              'message' => '暂无正在直播的老师',
          ];
        }
      }else{
                return [
              'success' => false,
              'data' => array(),
              'message' => 'Error 请求非GET请求或缺少参数',
          ];
      }
    }



    //设置用户额外字段
    public function AddUserField($res){
      $res['user_id'] = Admin::findOne($res['user_id'])->toArray();
     
      $cc = CcLive::find()->where('cc_id="'.$res['cc_id'].'"')->one();
       $res['user_id']['image'] = empty($res['user_id']['image'])?'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c':OSS::getUrl($cc['studio_id'],'picture','image',$res['user_id']['image']).Yii::$app->params['oss']['Size']['500x500'];
      $res['user_id']['nickName'] = $res['user_id']['name'];
      $res['pic_url_thumb'] = OSS::getUrl($cc['studio_id'],'picture','image',$res['pic_url']).Yii::$app->params['oss']['Size']['950x540'];
      $res['pic_url'] = OSS::getUrl($cc['studio_id'],'picture','image',$res['pic_url']).Yii::$app->params['oss']['Size']['950x540'];
      $res['templatetype'] = $cc['templatetype'];
      
      // if(isset($res['templatetype'])){
      //   unset($res['templatetype']);
      // }

      if(isset($res['checkurl'])){
        unset($res['checkurl']);
      }if(isset($res['barrage'])){
        unset($res['barrage']);
      }if(isset($res['foreignpublish'])){
        unset($res['foreignpublish']);
      }if(isset($res['openlowdelaymode'])){
        unset($res['openlowdelaymode']);
      }if(isset($res['showusercount'])){
        unset($res['showusercount']);
      }if(isset($res['status'])){
        unset($res['status']);
      }if(isset($res['publish_url'])){
        unset($res['publish_url']);
      }
      $query = UserFollow::find();
      $query->where(['follow_user_id'=> $res['user_id']['id'],'status' => 1]);
      $res['user_id']['user_fans'] = $query->count(1);
      $res['query'] =  $query->createCommand()->getRawSql();
      $res['is_follow'] = 0;
      if(!empty($_GET['user_id'])){
        $query = UserFollow::find();
        $query->where(['follow_user_id'=> $res['user_id']['id'],'status' => 1,'user_id'=>$_GET['user_id']]);
        $res['is_follow'] = $query->count(1);
      }
      // if($cc['studio_id']==183){
      $res['play_link'] = 'https://view.csslcloud.net/api/view/lecturer?roomid='.$res['cc_id'].'&userid=8FF243A3955D1B18';



      $res['share_link'] = 'http://www.meishuquanyunxiao.com/share/live-view.html?cc_id='.$res['cc_id'];
      if(!empty($res['live_id'])){
        $res['share_link'] .= "&live_id=".$res['id'];
      }
      // }else{
      //   $studio = Studio::findOne($cc['studio_id']);
      //   $res['share_link'] = 'http://www.meishuquanyunxiao.com/download/index.html?token_value='.$studio->token_value;
      // }
      // if($res['play_type'] == "pc"){
      //   $res['templatetype'] = 5;
      // }else{
      //   $res['templatetype'] = 3;
      // }
      $lenght = "";
      if(!empty($res['start_time']) && !empty($res['end_time'])){
        $lenght = strtotime($res['end_time'])-strtotime($res['start_time']);
        $lenght = date("H时i分s秒",$lenght-(8*60*60));
      }
      $cclive_type= array();
      $cclive_type[0] = array('name'=>'未分类','cclive_type'=>0);
      $cclive_type[1] = array('name'=>'素描','cclive_type'=>1);
      $cclive_type[11] = array('name'=>'素描头像','cclive_type'=>11);
      $cclive_type[12] = array('name'=>'素描静物','cclive_type'=>12);
      $cclive_type[13] = array('name'=>'半身像','cclive_type'=>13);
      $cclive_type[14] = array('name'=>'石膏像','cclive_type'=>14);
      $cclive_type[15] = array('name'=>'几何形体','cclive_type'=>15);

      $cclive_type[2] = array('name'=>'色彩','cclive_type'=>2);
      $cclive_type[21] = array('name'=>'色彩静物','cclive_type'=>21);
      $cclive_type[22] = array('name'=>'色彩头像','cclive_type'=>22);
      $cclive_type[23] = array('name'=>'色彩场景','cclive_type'=>23);

      $cclive_type[3] = array('name'=>'速写','cclive_type'=>3);
      $cclive_type[31] = array('name'=>'单人速写','cclive_type'=>31);
      $cclive_type[32] = array('name'=>'人物组合','cclive_type'=>32);
      $cclive_type[33] = array('name'=>'命题速写','cclive_type'=>33);
      $cclive_type[34] = array('name'=>'速写场景','cclive_type'=>34);

      $cclive_type[4] = array('name'=>'设计','cclive_type'=>4);
      $cclive_type[41] = array('name'=>'设计素描','cclive_type'=>41);
      $cclive_type[42] = array('name'=>'设计色彩','cclive_type'=>42);
      $cclive_type[43] = array('name'=>'创意速写','cclive_type'=>43);
      $cclive_type[44] = array('name'=>'设计基础','cclive_type'=>44);
      $cclive_type[45] = array('name'=>'创意设计','cclive_type'=>45);

      $cclive_type[5] = array('name'=>'专题','cclive_type'=>5);
      $cclive_type[51] = array('name'=>'讲大课','cclive_type'=>51);
      $cclive_type[52] = array('name'=>'文化课','cclive_type'=>52);
      $cclive_type[53] = array('name'=>'其他','cclive_type'=>53);
      $res['cclive_type'] = $cclive_type[$res['cclive_type']];


      if(empty($res['playpass'])){
        $res['playpass'] = (string)$cc->playpass;
      }

      if(empty($res['is_sideways'])){
        $res['is_sideways'] = 0;
      }

      if(empty($res['record_id'])){
        $res['record_id'] = "";
      }

      if(empty($res['publisherpass'])){
        $res['publisherpass'] = $cc->publisherpass;
      }
      $res['live_lenght'] = $lenght;
      return $res;
    }
}