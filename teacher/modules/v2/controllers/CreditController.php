<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\User;
use teacher\modules\v2\models\Credit;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\Common;

class CreditController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Credit';

    //获取学分记录
    public function actionGetList()
	{
		$modelClass = $this->modelClass;

		$user_id = Yii::$app->request->post('user_id');

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}
		
		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		//针对家长特殊处理
		if(empty($user_id)){
			$account = Common::getAccount('family', $account_id);
			$user_id =  $account->relation_id;
		}

		$list = $modelClass::createData($user_id, $page, $limit);

		//判断是否为可操作人
		$user = User::findOne($user_id);
		$is_oper = $modelClass::isOperation($user_role, $account_id, $user);

		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

		return [
			'list' => $list,
			'others' => [
				'add' => ($is_oper) ? '添加' : NULL,
				'clear' =>  ($is_oper) ? '清空记录' : NULL,
				'current' => $user->credit,
			]
		];
	}

	//增加学分记录
	public function actionCreate()
    {
      	$model = new Credit();
		if($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Add Success');
			$user = User::findOne($model->user_id);
			$user->credit += $model->credit;
			if($user->save()){
				return [];
			}
		}
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }

    public function actionClear()
    {
      	$modelClass = $this->modelClass;

		$user_id = Yii::$app->request->post('user_id');
		if(!$user_id){
			return SendMessage::sendErrorMsg('user_id不能为空');
		}

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		//判断是否为可操作人
		$user = User::findOne($user_id);
		$is_oper = $modelClass::isOperation($user_role, $account_id, $user);
		if($is_oper){
			if($modelClass::updateAll(['status' => $modelClass::STATUS_DELETED], ['user_id' => $user_id])){
				$user->credit = 100;
				if($user->save()){
					$_GET['message'] = Yii::t('teacher', '清空成功!');
				}
			}
		}else{
			$_GET['message'] = Yii::t('teacher', '您没有清空权限!');
		}
		
		return [];
    }

    public function actionDelete()
    {
      	$modelClass = $this->modelClass;

		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$user_id = Yii::$app->request->post('user_id');
		if(!$user_id){
			return SendMessage::sendErrorMsg('user_id不能为空');
		}

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		//判断是否为可操作人
		$user = User::findOne($user_id);
		$is_oper = $modelClass::isOperation($user_role, $account_id, $user);
		if($is_oper){
			$model = $this->findModel($id);
			$model->status = $modelClass::STATUS_DELETED;
			if($model->save()){
				$user->credit -= $model->credit;
				if($user->save()){
					$_GET['message'] = Yii::t('teacher', '删除成功!');
				}
			}
		}else{
			$_GET['message'] = Yii::t('teacher', '您没有删除权限!');
		}

		return [];
    }


    protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}