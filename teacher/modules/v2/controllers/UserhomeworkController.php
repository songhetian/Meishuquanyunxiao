<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use components\Upload;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Campus;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Family;
use teacher\modules\v1\models\UserClass;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\UserHomeWork;

class UserhomeworkController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\UserHomeWork';

	//上传作业 -- sutdent
	public function actionCreate()
    {
        $modelClass = $this->modelClass;
        $model = new UserHomeWork(['scenario' => 'create']);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $studio = User::findOne($model->user_id)->studio_id;
        $model->image = Upload::pic_upload($_FILES, $studio, 'user-homework', 'image')['image'];
        if ($model->save()) {

            $_GET['message'] = Yii::t('api', 'UserHomework Create Success');
            return $modelClass::findOne($model->id);
        } else {
            return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }

	//上传作业 -- sutdent
	public function actionCreateTest()
    {
        $modelClass = $this->modelClass;

        $model = new UserHomeWork(['scenario' => 'create']);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->save()) {

            $_GET['message'] = Yii::t('api', 'UserHomework Create Success');
            return $modelClass::findOne($model->id);
        } else {
            return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }
    //作业上传
    public  function actionUpload() {
    	$user_id = Yii::$app->request->post('user_id'); 
 		$studio   = Campus::findOne(\common\models\User::findOne($user_id)->campus_id)->studio_id;
        $_GET['message'] = "图片上传成功";
        return $image = Upload::pic_upload($_FILES, $studio, 'user-homework', 'image')['image'];
    }

    
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


	//我的批改and我的作业
	public function actionGetList($admin_id,$user_role,$page=0,$limit=10) {

		$modelClass = $this->modelClass;

		if($user_role == 'teacher') {
			$list = $modelClass::find()->select('user_id')->where(['evaluator'=>$admin_id,'status'=>10])->asArray()->all();
			$users = array_unique(array_column($list, 'user_id'));
	
		}elseif($user_role == 'student'){
			$users = $admin_id;
		}elseif($user_role == 'family') {
			$users = Family::findOne($admin_id)->relation_id;
		}
		$offset = $page*$limit;
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		$homeworks = array();
		if($users){
			$homeworks =  User::find()
								->select('user.id,name')
								->joinWith('homeworks')
								->where(['user.id'=>$users,'homeworks.status'=>10])
								->offset($offset)
								->limit($limit)
								->asArray()
								->all();
									
			foreach ($homeworks as $key => $value) {
				if($user_role == 'teacher'){
					$homeworks[$key]['homeworks'] =$this->homework($value['id'],$admin_id);
				}else{
					$homeworks[$key]['homeworks'] =$this->homework($value['id']);
				}
			}
		}

		return $homeworks;
	}
	//返回学生作业列表
	public function homework($user_id,$evaluator='') {
		return  \teacher\modules\v2\models\UserHomeWork::find()
							->where(['user_id'=>$user_id,'status'=>UserClass::STATUS_ACTIVE])
							->andFilterWhere(['evaluator'=>$evaluator])
							->orderBy('video DESC,score DESC')
							->all();
	}
	public function findModel($id)
	{
		$modelClass = $this->modelClass;

		return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
	}
}
?>