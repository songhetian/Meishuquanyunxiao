<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\ErrorLog;
use teacher\modules\v2\models\SendMessage;

class ErrorController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\ErrorLog';


    //添加
    public function actionAdd()
    {
    	$modelClass = $this->modelClass;

    	$model = new ErrorLog();
    
    	if($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {

    		return SendMessage::sendSuccessMsg("添加成功");
    	}else{
    		return SendMessage::sendErrorMsg("添加失败");
    	}
    }

    //获取列表
    public function actionList($admin_id='',$role='',$studio_name='',$page=0,$limit=20) {
       $modelClass = $this->modelClass;
       $offset = $page*$limit;

       if($admin_id == 0) {
       	   $admin_id = '';
       }

       if($role == 0) {
       	   $role = '';
       }

       $_GET['message'] = "获取列表成功";
       return  $modelClass::find()	
       					    ->joinWith("studios")
       					    ->where(['error_log.status'=>10])
       						->andFilterWhere(['error_log.admin_id'=>$admin_id,'error_log.role'=>$role])
       						->andFilterWhere(['like', 'studios.name', $studio_name])
       						->orderBy('created_at DESC')
       						->offset($offset)
       						->limit($limit)
       					    ->all();
	}

	//获取详情

	public function actionGetInfo($id) {
		$modelClass = $this->modelClass;
		 $_GET['message'] = "获取详情成功";
		return $modelClass::findOne($id);
	}
}