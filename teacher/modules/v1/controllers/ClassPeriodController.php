<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class ClassPeriodController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\ClassPeriod';

	/**
	 *[获取上课时间]
	 *
	 *@param $studio_id [画室id]
	 *
	 *@return mixed[array]
	*/	


	public function actionView($studio_id)
	{
		$modelClass = $this->modelClass;

		$query = $modelClass::find()->where(['studio_id'=>$studio_id,'status'=>$modelClass::STATUS_ACTIVE])->orderBy('position');
		$_GET['message'] = Yii::t('teacher','Sucessfully ClassPeriod List');
		return new ActiveDataProvider([
			'query'=>$query
		]);
	}

	public function findModel($id)
	{
		$modelClass = $this->modelClass;

		return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
	}
}
?>