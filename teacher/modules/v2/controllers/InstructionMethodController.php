<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class InstructionMethodController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\InstructionMethod';

	public function actionGetList()
	{
		$modelClass = $this->modelClass;

		$query = $modelClass::find()
					 ->select('id,name')
					 ->where(['type'=>$this->studio_type])
					 ->andFilterWhere(['status' => $modelClass::STATUS_ACTIVE]);

		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		return new ActiveDataProvider([
			'query'=>$query
		]);

	}
}
?>