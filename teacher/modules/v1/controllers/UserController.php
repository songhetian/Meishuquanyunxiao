<?php 
namespace teacher\modules\v1\controllers;

use yii\data\ActiveDataProvider;

class UserController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\User';

	/*
	 *[获取学生所有作业]
	 *@param user_id 学生id
	*/

	public function actionHomeworks($user_id)
	{
		$modelClass = $this->modelClass;

		$query = $modelClass::find()->select('id,name')->where(['id'=>$user_id]);
		return new ActiveDataProvider([
            'query' => $query
        ]);
	}
}
?>