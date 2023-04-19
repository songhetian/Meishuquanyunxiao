<?php 
namespace teacher\modules\v1\controllers;

use Yii;

class DandelionController extends MainController {

	public $modelClass = 'teacher\modules\v1\models\DandelionInfo';

	public function actionIndex($build_id) {

		$modelClass = $this->modelClass;

		$app_id = !empty($app_id = $modelClass::find()->where(['build_id'=>$build_id])->one()['app_id'])? $app_id : "";
		return array('app_id'=>$app_id);
	}
}
?>