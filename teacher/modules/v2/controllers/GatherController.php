<?php
namespace teacher\modules\v2\controllers;

use Yii;
use components\Upload;
use yii\data\ActiveDataProvider;
use common\models\Studio;
use teacher\modules\v2\models\User;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Campus;
use teacher\modules\v2\models\Gather;
use teacher\modules\v2\models\BuyRecord;
use teacher\modules\v1\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;


class GatherController extends MainController
{


    public $modelClass = 'teacher\modules\v2\models\Gather';

    /*
    *[actionCreate]  云课件创建
    *@param 
    *STATUS_ACTIVE
    */
    public function actionCreate() {

        $modelClass = $this->modelClass;
        $studio = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id;
        $image = Upload::pic_upload($_FILES, $studio, 'picture', 'image');
        $model = new Gather();
        if($model->load(Yii::$app->getRequest()->getBodyParams(),'')) {
            if(!Yii::$app->request->post('price')) {
                $model->price = 0;
            }
            $model->studio_id = $studio;
            $model->image     = $image['image'];
            $model->status    = $modelClass::STATUS_DELETED;
            $model->author    = Yii::$app->request->post('admin_id');
            $model->scenario  = 'create';
            if($model->save()){
                $_GET['message'] = Yii::t('teacher','Cloud Create Success');
                return $model;
            }else{
                return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Cloud Create Fail'));
            }
        }
    }
    public function actionCreateTest() {

        $modelClass = $this->modelClass;
        $studio = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id;
        $model = new Gather();
        if($model->load(Yii::$app->getRequest()->getBodyParams(),'')) {
            if(!Yii::$app->request->post('price')) {
                $model->price = 0;
            }
            $model->studio_id = $studio;
            $model->status    = $modelClass::STATUS_DELETED;
            $model->author    = Yii::$app->request->post('admin_id');
            $model->scenario  = 'create';
            if($model->save()){
                $_GET['message'] = Yii::t('teacher','Cloud Create Success');
                return $model;
            }else{
                return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Cloud Create Fail'));
            }
        }
    }
    public  function actionUpload() {

        #$studio = Campus::findOne(Admin::findOne(Yii::$app->request->post('admin_id'))->campus_id)->studio_id; 
        $studio = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id; 
        $_GET['message'] = "图片上传成功";
        return $image = Upload::pic_upload($_FILES, $studio, 'picture', 'image')['image'];


    }

    /*
    *  【课件包绑定课件】
    *
    */
    public function actionBandCourseMaterial($cloud_id,$course_material_id) {

        $modelClass = $this->modelClass;

        $model = $this->findModel($cloud_id);

        $model->course_material_id = $course_material_id;

        $model->status  = $modelClass::STATUS_ACTIVE;

        if($model->save()) {
            return  SendMessage::sendSuccessMsg(Yii::t('teacher','Cloud Create Success'));
        }else{
            return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Cloud Create Fail'));
        }
    }

    /*     
    *[actionCloudList]  获取出版社云课件
    *@param 
    *
    */
    public function actionCloudList($admin_id = '' , $page = 0, $limit = 3 , $type = 1 ,$category_id = '') {

        $modelClass = $this->modelClass;

        if($category_id == 0) {

            $category_id = '';
        }

        if($admin_id == 0){
          $admin_id = '';
        }

        $studio_id = $this->studio_id;
        
        $studio =  array_keys(Studio::find()->where(['is_press'=>1,'status'=>Studio::STATUS_ACTIVE])->indexBy('id')->all());

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        if($type == $modelClass::IS_NEW){
           $list =   $modelClass::find()
                         ->where(['studio_id'=>$studio,'status'=>$modelClass::STATUS_ACTIVE])
                         ->andFilterWhere(['author'=>$admin_id])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('created_at DESC,updated_at DESC')
                         ->all();
        }else{
           $list =   $modelClass::find()
                         ->where(['studio_id'=>$studio,'status'=>$modelClass::STATUS_ACTIVE])
                         ->andFilterWhere(['author'=>$admin_id])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('watch_number DESC')
                         ->all();
        }
        foreach ($list as $key => $value) {
          if(BuyRecord::GetBuyStudio($value->id,$studio_id,$this->user_id) || $value->price == 0.00) {
              $list[$key]['is_buy'] = true;
          }
        }
        return $list;
      
    }

    //云课件获取教案列表
    public function actionCloudCourseList($admin_id,$page=0,$limit=10) {

        $modelClass = $this->modelClass;
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return \teacher\modules\v2\models\CourseMaterial::getCourseList($admin_id,$page,$limit);       
    }

    //观看数量+1
    public function actionCloudCount($cloud_id) {

        $modelClass = $this->modelClass;

        $model = $modelClass::findOne($cloud_id);

        $model->updateCounters(['watch_number' => 1]);
    }

    //云课件获取名师
    public function actionCloudTeacher() {

        $modelClass = $this->modelClass;
        $_GET['message'] = Yii::t('teacher','Sucessfully List');

        $admins =  Admin::find()
                      ->select(['admin_id'=>'id','name'])
                      ->where(['id'=>$modelClass::getTeacher(),'studio_id'=>183])
                      ->asArray()
                      ->all();
        array_unshift($admins,array('admin_id'=>0,'name'=>'全部'));

        return $admins;
       
    }


       /*
    *[actionCloudList]  获取云课堂
    *@param 
    *
    */

    public function actionCloudRoomList($admin_id='', $page = 0, $limit = 3 , $type = 1 ,$category_id = '') {

        $modelClass  = $this->modelClass;

        if($category_id == 0) {

            $category_id = '';
        }
        if($admin_id == 0){

          $admin_id = '';
          
        }
        if($this->user_type == Yii::$app->params['teacherRole']) {
            $studio_id =  \common\models\Campus::findOne(Admin::findOne($this->user_id)->campus_id)->studio_id;
        }else if($this->user_type == Yii::$app->params['studentRole']){
            $studio_id =  User::findOne($this->user_id)->studio_id;
        }else if($this->user_type == Yii::$app->params['familyRole']) {
            $studio_id =  Family::findOne($this->user_id)->studio_id;
        }
        if(Studio::findOne($studio_id)->is_press == 1) {
            $modelClass::$press  = true;
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        $modelClass::$buy_id = $this->user_id;
        $modelClass::$user_role = $this->user_type;
        if($type == $modelClass::IS_NEW){
           return  $modelClass::find()
                         ->where(['studio_id'=>$studio_id,'status'=>$modelClass::STATUS_ACTIVE])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('created_at DESC,updated_at DESC')
                         ->all();
        }else{
           return  $modelClass::find()
                         ->where(['studio_id'=>$studio_id,'status'=>$modelClass::STATUS_ACTIVE])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('watch_number DESC')
                         ->all();
        }
    }

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}