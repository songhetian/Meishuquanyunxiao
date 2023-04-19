<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Studio;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Gather;
use teacher\modules\v2\models\BuyRecord;
use teacher\modules\v1\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;
use teacher\modules\v2\models\SimpleGather;

class NewGatherController extends MainController
{


    public $modelClass = 'teacher\modules\v2\models\SimpleGather';

    /*     
    *[actionCloudList]  获取出版社云课件
    *@param 
    *
    */
    public function actionCloudList($admin_id = '' , $page = 0, $limit = 3 , $type = 1 ,$category_id = '',$version= '') {

        if($this->studio_id == 319) {
            
             $_GET['message'] = Yii::t('teacher','Sucessfully List');

             return array();
        }

        $modelClass = $this->modelClass;

        if($category_id == 0) {

            $category_id = '';
        }

        if($admin_id == 0){
          $admin_id = '';
        }
        if($this->studio_id == 183) {
            $public = array(10);
        }else{
            $public = array(0,10);
        }

        $studio_id = $this->studio_id;
        
        $studio =  array_keys(Studio::find()->where(['is_press'=>1,'status'=>Studio::STATUS_ACTIVE])->indexBy('id')->all());

        $_GET['message'] = Yii::t('teacher','Sucessfully List');

        SimpleGather::$buy_id    = $this->user_id;
        SimpleGather::$user_role = $this->user_type;
        SimpleGather::$press     = Studio::findOne($this->studio_id)->is_press;

        // if($version) {
        //      SimpleGather::$version  = $version;
        // }

        if($type == $modelClass::IS_NEW){
           $list =   $modelClass::find()
                         ->where(['studio_id'=>$studio,'status'=>$modelClass::STATUS_ACTIVE,'is_public'=>$public])
                         ->andFilterWhere(['author'=>$admin_id])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('created_at DESC,updated_at DESC')
                         ->all();
        }else{
           $list =   $modelClass::find()
                         ->where(['studio_id'=>$studio,'status'=>$modelClass::STATUS_ACTIVE,'is_public'=>$public])
                         ->andFilterWhere(['author'=>$admin_id])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('watch_number DESC')
                         ->all();
        }
        foreach ($list as $key => $value) {

            if($this->user_role == 10) {
              if(BuyRecord::GetBuyStudio($value->id,$studio_id,$this->user_id) || $value->price == 0.00) {
                  $list[$key]['is_buy'] = true;
                }
            }
        }
        return $list;
      
    }

    /*
    *[actionCloudList]  获取云课堂
    *@param 
    *
    */

    public function actionCloudRoomList($page = 0, $limit = 10 , $type = 1 ,$category_id = '') {

        $modelClass  = $this->modelClass;

        if($category_id == 0) {

            $category_id = '';
        }

        if($this->studio_id == 183) {
            $public = array(10);
        }else{
            $public = array(0,10);
        }

        $studio_id = $this->studio_id;
        
        if(Studio::findOne($studio_id)->is_press == 1) {
            $modelClass::$press  = true;
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        $modelClass::$buy_id = $this->user_id;
        $modelClass::$user_role = $this->user_type;
        if($type == $modelClass::IS_NEW){
           return  $modelClass::find()
                         ->where(['studio_id'=>$studio_id,'status'=>$modelClass::STATUS_ACTIVE,'is_public'=>$public])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('created_at DESC,updated_at DESC')
                         ->all();
        }else{
           return  $modelClass::find()
                         ->where(['studio_id'=>$studio_id,'status'=>$modelClass::STATUS_ACTIVE,'is_public'=>$public])
                         ->andFilterWhere(['category_id'=>$category_id])
                         ->offset($page*$limit)
                         ->limit($limit)
                         ->orderBy('watch_number DESC')
                         ->all();
        }
    }

    //获取教案列表
    public function actionGetList($cloud_id,$is_show = 0) {


      $modelClass = $this->modelClass;

      $model = $this->findModel($cloud_id);

      $course_material_id = $model->course_material_id;

      $modelClass::$show = $is_show;

      $_GET['message'] = "获取列表成功";

      return $modelClass::GetMaterialList($course_material_id);
    }

    //云课件详情

    public function actionInfo($cloud_id) {

          $modelClass = $this->modelClass;

          SimpleGather::$buy_id    = $this->user_id;
          SimpleGather::$user_role = $this->user_type;

          $model = SimpleGather::findOne($cloud_id);

          $_GET['message'] = "获取信息成功";

          return $model;
    }


    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}