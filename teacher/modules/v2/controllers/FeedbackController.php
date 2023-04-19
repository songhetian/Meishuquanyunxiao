<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Feedback;
use common\models\Format;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Family;
use components\Upload;

class FeedbackController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Feedback';

	public function actionCreate()
    {
      	$model = new Feedback();
		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Feedback Success');
			return $this->findModel($model->id);
		}else{
			return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
		}
    }

    //上传反馈图片
    public function actionUpload()
    {
    	$type = Yii::$app->request->post('type');
    	$feedback_id = Yii::$app->request->post('feedback_id');
    	if(!$type){
			return SendMessage::sendErrorMsg('type不能为空');
		}
		if(!$feedback_id){
			return SendMessage::sendErrorMsg('feedback_id不能为空');
		}
		switch ($type) {
			case 'student':
				$feedback = User::findOne($feedback_id);
				break;
			case 'teacher':
				$feedback = Admin::findOne($feedback_id);
				break;
			case 'family':
				$feedback = Family::findOne($feedback_id);
				break;
		}
		if(!$feedback){
			return SendMessage::sendErrorMsg('反馈人不存在');
		}
		$studio = Campus::findOne($feedback->campus_id)->studio_id;
		$images = Upload::pic_upload($_FILES, $studio, 'feedback', 'image');
		$_GET['message'] = "图片上传成功";
		return Format::implodeValue($images);
    }
    
    protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}