<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Campus;
use teacher\modules\v1\models\SendMessage;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\UserHomework;
use teacher\modules\v1\models\UserClass;
use components\Upload;

class UserhomeworkController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\UserHomeWork';
	/**
	 *[作业点评]
	 *
	 *@param post [comments 评语,score 得分]
	 *
	*/
	public function actionScore()
	{	
		$homework_id = Yii::$app->getRequest()->getBodyParams()['homework_id'];
		
		$model = $this->findModel($homework_id);

		if($model)
		{ 
			 $model->load(Yii::$app->getRequest()->getBodyParams(),'');
			 if($model->save())
			 {
			 	return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Score Success'));
			 }else{
			 	return SendMessage::sendVerifyErrorMsg($model, Yii::t('teacher', 'Verify Error'));
			 }
		}else{
			return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Home Not Exist'));
		}
	}		

	/**
	 *[作业点评]
	 *
	 *@param post [comments 评语,score 得分]
	 *
	*/
	public function actionScoreTest()
	{	

		$modelClass = $this->modelClass;

		$homework_id = Yii::$app->getRequest()->getBodyParams()['homework_id'];

		$model = $this->findModel($homework_id);
		if($_FILES) {
			$evaluator  = Yii::$app->request->post('evaluator');

			$studio = $modelClass::GetStudio($evaluator);

	        $image = Upload::pic_upload($_FILES, $studio, 'user-homework', 'image2wbmp(image)');

			$model->comment_image = $image['image'];
		}

		if($model)
		{ 
			 $model->load(Yii::$app->getRequest()->getBodyParams(),'');
			
			 if($model->save())
			 {
			 	return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Score Success'));
			 }else{
			 	return SendMessage::sendVerifyErrorMsg($model, Yii::t('teacher', 'Verify Error'));
			 }
		}else{
			return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Home Not Exist'));
		}
	}

	public function actionList($user_id,$course_id = '') {

		$modelClass = $this->modelClass;

		$list = $modelClass::find()
					->where(['user_id'=>$user_id,'status'=>$modelClass::STATUS_ACTIVE])
					->andFilterWhere(['course_id'=>$course_id])
					->orderBy('created_at DESC')
					->all();

	    $_GET['message'] = Yii::t('teacher','Sucessfully List');
		return $list;
	}

	public function findModel($id)
	{
		$modelClass = $this->modelClass;

		return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
	}
}
?>