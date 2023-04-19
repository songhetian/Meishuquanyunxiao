<?php
namespace teacher\modules\v2\controllers;

use Yii;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Gather;
use teacher\modules\v2\models\BuyRecord;
use teacher\modules\v2\models\SimpleGather;


class BuyRecordController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\BuyRecord';
	
    //购买课件搜索
    public function actionList($admin_id , $user_role ,$studio_id ,$category_id='' , $author='' , $page = 0, $limit = 3) {
        if($category_id == 0 ){
            $category_id = '';
        }

        if($author == 0) {
            $author = '';
        }
        $modelClass = $this->modelClass;
        if($user_role == 'teacher'){
            $admin = Admin::findOne($admin_id);
            if(!$admin->is_cloud) {
               $_GET['message'] = Yii::t('teacher','Sucessfully List');
               return array();
            }

            $BuyList =  $modelClass::find()
                              ->joinWith('gathers')
                              ->where(['buy_record.buy_studio'=>$studio_id,'buy_record.status'=>10])
                              ->andWhere(['>', 'buy_record.active_at', time()])
                              ->andFilterWhere(['gathers.category_id'=>$category_id])
                              ->andFilterWhere(['gathers.author'=>$author])
                              ->asArray()
                              ->all();
        }elseif($user_role == 'student'){
            $BuyList =  $modelClass::find()
                              ->joinWith('gathers')
                              ->where(['buy_record.buy_id'=>$admin_id,'buy_record.status'=>10])
                              ->andwhere(['>', 'buy_record.active_at', time()])
                              ->andFilterWhere(['gathers.category_id'=>$category_id])
                              ->andFilterWhere(['gathers.author'=>$author])
                              ->asArray()
                              ->all();
        }

        //获取价格为0的云课件
        $FreeGathers =  SimpleGather::find()
                          ->select('id')
                          ->where(['price'=>0,'studio_id'=>183,'status'=>10])
                          ->andFilterWhere(['category_id'=>$category_id])
                          ->andFilterWhere(['author'=>$author])
                          ->asArray()
                          ->all();

        $FressIds  =  array_column($FreeGathers,'id');

        $Buys   =  array_column($BuyList,'gather_id');

        $offset = ($page*$limit);

        $Meage  =  array_unique(array_merge($FressIds,$Buys));

        $GatherIds = array_slice($Meage,$offset,$limit);

       
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
       # $gathers =   SimpleGather::findAll(['id'=>$GatherIds]);

        $gathers = SimpleGather::find()
                                  ->where(['id'=>$GatherIds])
                                  ->orderBy("created_at desc")
                                  ->all();
        if($user_role == 'teacher'){
            foreach ($gathers as $key => $value) {
               $id = $value->id;
               $gathers[$key]['is_buy'] = true;
               $gathers[$key]['activetime'] = date('Y-m-d',$modelClass::findOne(['buy_studio'=>$studio_id,'gather_id'=>$id])->active_at);
               
            }
            return $gathers;
        }elseif($user_role == 'student'){
            foreach ($gathers as $key => $value) {
               $id = $value->id;
               $gathers[$key]['is_buy'] = true;
               $gathers[$key]['activetime'] = date('Y-m-d',$modelClass::findOne(['buy_id'=>$admin_id,'gather_id'=>$id])->active_at);
            }
            return $gathers;
        }

    }

    //购买课件搜索(优化)
    public function actionGetList($category_id='' , $author='' , $page = 0, $limit = 3) {
        if($category_id == 0 ){
            $category_id = '';
        }

        if($author == 0) {
            $author = '';
        }
        $modelClass = $this->modelClass;
        $admin_id   = $this->user_id;
        $user_role  = $this->user_type;
        $studio_id  = $this->studio_id;

        if($user_role == 'teacher'){
            $admin = Admin::findOne($admin_id);
            if(!$admin->is_cloud) {
               $_GET['message'] = Yii::t('teacher','Sucessfully List');
               return array();
            }

            $BuyList =  $modelClass::find()
                              ->joinWith('gathers')
                              ->where(['buy_record.buy_studio'=>$studio_id,'buy_record.status'=>10])
                              ->andWhere(['>', 'buy_record.active_at', time()])
                              ->andFilterWhere(['gathers.category_id'=>$category_id])
                              ->andFilterWhere(['gathers.author'=>$author])
                              ->asArray()
                              ->all();
        }elseif($user_role == 'student'){
            $BuyList =  $modelClass::find()
                              ->joinWith('gathers')
                              ->where(['buy_record.buy_id'=>$admin_id,'buy_record.status'=>10])
                              ->where(['>', 'buy_record.active_at', time()])
                              ->andFilterWhere(['gathers.category_id'=>$category_id])
                              ->andFilterWhere(['gathers.author'=>$author])
                              ->asArray()
                              ->all();
        }
   
        //获取价格为0的云课件
        $FreeGathers =  SimpleGather::find()
                          ->select('id')
                          ->where(['price'=>0,'studio_id'=>183,'status'=>10])
                          ->andFilterWhere(['category_id'=>$category_id])
                          ->andFilterWhere(['author'=>$author])
                          ->asArray()
                          ->all();

        $FressIds  =  array_column($FreeGathers,'id');

        $Buys =  array_column($BuyList,'gather_id');

        $offset = ($page*$limit);

        $Meage  =  array_unique(array_merge($FressIds,$Buys));

        $GatherIds = array_slice($Meage,$offset,$limit);

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        $gathers = SimpleGather::find()
                                  ->where(['id'=>$GatherIds])
                                  ->orderBy("created_at desc")
                                  ->all();
        if($user_role == 'teacher'){
            foreach ($gathers as $key => $value) {
               $id = $value->id;
               $gathers[$key]['is_buy'] = true;
               $gathers[$key]['activetime'] = date('Y-m-d',$modelClass::findOne(['buy_studio'=>$studio_id,'gather_id'=>$id])->active_at);
               
            }
            return $gathers;
        }elseif($user_role == 'student'){
            foreach ($gathers as $key => $value) {
               $id = $value->id;
               $gathers[$key]['is_buy'] = true;
               $gathers[$key]['activetime'] = date('Y-m-d',$modelClass::findOne(['buy_id'=>$admin_id,'gather_id'=>$id])->active_at);
            }
            return $gathers;
        }

    }



    //获取已购买老师
    public function actionGetAdmins($admin_id,$studio_id,$user_role) {
        $modelClass = $this->modelClass;

        if($user_role == 'teacher'){
            $BuyList =  $modelClass::find()
                              ->where(['buy_studio'=>$studio_id,'status'=>10])
                              ->where(['>', 'active_at', time()])
                              ->asArray()
                              ->all();
        }elseif($user_role == 'student'){
            $BuyList =  $modelClass::find()
                              ->asArray()
                              ->where(['buy_id'=>$admin_id,'status'=>10])
                              ->where(['>', 'active_at', time()])
                              ->all();
        }
        $GatherIds =  array_column($BuyList,'gather_id');
        $Gathers = Gather::find()
                           ->select('author')
                           ->where(['id'=>$GatherIds])  
                           ->asArray()
                           ->all();  
        $AdminIds =  array_column($Gathers,'author');

        $FreeList = SimpleGather::find()
                       ->select('author')
                       ->where(['price'=>0,'studio_id'=>183,'status'=>10])
                       ->asArray()
                       ->all();

        $FreeIds  = array_unique(array_column($FreeList,'author'));

        $AdminIds = array_merge($FreeIds,$AdminIds);
   
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return Admin::find()
                      ->select(['admin_id'=>'id','name'])
                      ->where(['id'=>$AdminIds])
                      ->orderBy('studio_id desc')
                      ->asArray()
                      ->all();
    }

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}