<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\SendMessage;

class ClassPeriodController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\ClassPeriod';


	//获取上课时间
	public function actionView($studio_id)
	{
		$modelClass = $this->modelClass;

		$query = $modelClass::find()
		->where(['studio_id' => $studio_id, 'status' => $modelClass::STATUS_ACTIVE])
		->orderBy('position');

		$_GET['message'] = Yii::t('teacher','Sucessfully ClassPeriod List');

		return new ActiveDataProvider([
			'query' => $query
		]);
	}

	//获取上课时间 For FuWen
	public function actionGetTime()
	{
		$studio_id = Yii::$app->request->post('studio_id');
		if(!$studio_id){
			return SendMessage::sendErrorMsg('studio_id不能为空');
		}
		$modelClass = $this->modelClass;

		$times = $modelClass::find()
		->where(['studio_id' => $studio_id])
		->orderBy('position')
		->all();

		foreach ($times as $v) {
			$res[] = [
				'id' => $v->id,
				'title' => $v->name,
				'beginTime' => $v->started_at,
				'endTime' => $v->dismissed_at,
				'isAcitve' => ($v->status == $modelClass::STATUS_ACTIVE) ? true : false,
				'isAcitveText' => ($v->status == $modelClass::STATUS_ACTIVE) ? '启用' : '禁用'
			];
		}
		$_GET['message'] = Yii::t('teacher','Sucessfully ClassPeriod List');

		return $res;
	}

	//更新上课时间
	public function actionUpdate()
    {
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id');
		}
      	$model = $this->findModel($id);
		if($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
			if($model->save()){
				$_GET['message'] = "修改成功";
				return [];
			}
		}
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }
    
    //更新详情
    public function actionGetTimeInfo()
    {
    	$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id');
		}

		$modelClass = $this->modelClass;
		$model = $modelClass::find()
		->select(['id', 'name', 'started_at', 'dismissed_at'])
		->where(['id' => $id])
		->asArray()
		->one();
		
		$info['id']   = $id;
		$info['title'] = $model['name'];
		$info['beginTime'] = $model['started_at'];
		$info['endTime'] = $model['dismissed_at'];

		$_GET['message'] = "Sucessfully Get List";

		return $info;
    }

    public function actionDelete()
	{
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$modelClass = $this->modelClass;
		$model = $this->findModel($id);
		if($model->status == $modelClass::STATUS_ACTIVE){
			if($model->updateStatus()){
				$_GET['message'] = "禁用成功";
			}
		}else{
			if($model->recoveryStatus()){
				$_GET['message'] = "启用成功";
			}
		}
		return [];
	}

	public function findModel($id)
	{
		$modelClass = $this->modelClass;

		return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
	}
}
?>