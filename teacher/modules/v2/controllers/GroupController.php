<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Format;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\Group;
use teacher\modules\v1\models\Errors;
use teacher\modules\v2\models\NewClasses;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\CourseMaterial;

class GroupController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Group';

    /**
     * [actionIndex 获取教案分组]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $course_material_id [教案ID]
     * @param     integer       $type               [类型] 10为图片 20为视频
     */
    
	public function actionIndex($course_material_id, $type)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where([
            'course_material_id' => $course_material_id, 
            'type' => $type, 
            'status' => $modelClass::STATUS_ACTIVE
        ]);
       
        $_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
	            'pagesize' => -1,
	        ]
        ]);
    }

	public function actionCreate()
	{
		$model = new Group();

		$course_material_id =  Yii::$app->request->post('course_material_id');

		$TeacherList =  NewClasses::getCourseAdminList($course_material_id);

		if(!in_array($this->user_id, $TeacherList)) {
			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));

		}

		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save())
		{
			return $model;
		}else{
			return $model->getErrors();
		}

	}
	/**
	 *【分组删除】
	 *@param group_id inter 分组id
	 *
	 *
	*/
	public function actionGroupDelete($group_id,$admin_id='')
	{
		$modelClass = $this->modelClass;

		$model = $this->findModel($group_id);

		$course_material_id  = $model['course_material_id'];

		#$create_id = CourseMaterial::findOne($course_material_id)['admin_id'];

		$TeacherList =  NewClasses::getCourseAdminList($course_material_id);

		if(!in_array($this->user_id, $TeacherList)) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));

		}

		if($model){
			if($model->updateStatus()) {

				return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));

			}else{
				return SendMessage::sendErrorMsg(Yii::t('teacher','Handle Field'));
			}
		}else{
			return SendMessage::sendErrorMsg(Yii::t('teacher','Group Not Exist'));
		}
	}


	/**
	 *【多分组删除】
	 *@param group_id inter 分组id
	 *
	 *
	*/
	public function actionGroupMoreDelete($group_id,$admin_id='')
	{
		$modelClass = $this->modelClass;

		$course_material_id  = $modelClass::findOne(explode(',',$group_id)[0])['course_material_id'];

		$TeacherList =  NewClasses::getCourseAdminList($course_material_id);

		if(!in_array($this->user_id, $TeacherList)) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));

		}

		$connect = Yii::$app->db->beginTransaction();
		try{
			$modelClass::delMore($group_id);
			 $connect->commit();
			 return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));
		} catch (Exception $e) {
		    $connect->rollBack();
		    return SendMessage::sendErrorMsg(Yii::t('teacher','Handle Field'));
		}
	}
	/**
	 *[分组资源删除]
	 *
	 *@param group_id inter 分组id source_id string  删除id 
	 *
	*/
	public function actionSourceDelete($group_id,$source_id,$admin_id='')
	{
		$modelClass = $this->modelClass;

		$model = $this->findModel($group_id);

		$course_material_id  = $model['course_material_id'];

		$TeacherList =  NewClasses::getCourseAdminList($course_material_id);

		if(!in_array($this->user_id, $TeacherList)) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));

		}


		$material_library_id =  Format::deleteFilterString($model->material_library_id,$source_id);

		$model->material_library_id = $material_library_id;

		if($model->save())
		{
			return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));
		}else{
			return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Handle Field'));
		}
	}
	/**
	 *[剪切接口]
	 *
	 *@param group_id 目标分组ID source_id 资源id type 类型
	 *
	*/
	public function actionCut($origin_group,$target_group,$source_id)
	{
		$modelClass = $this->modelClass;

		$origin_group = $this->findModel($origin_group);

		$origin_material_library_id =  Format::deleteFilterString($origin_group->material_library_id,$source_id);

		$origin_group->material_library_id = $origin_material_library_id;

		$target_group = $this->findModel($target_group);

		$target_material_library_id = Format::addFilterString($target_group->material_library_id,$source_id);
		$target_group->material_library_id = $target_material_library_id;

        $connect = Yii::$app->db->beginTransaction();
        try {

        	if(!$origin_group->save()) {

	    		throw new ErrorException(Errors::getInfo($origin_group->getErrors()));
	    	}
	    	if(!$target_group->save()) {

	    		throw new ErrorException(Errors::getInfo($target_group->getErrors()));
	    	}
        	$connect->commit();
        	return SendMessage::sendSuccessMsg(Yii::t('teacher','Cut Success'));
		} catch (Exception $e) {
		    $connect->rollBack();
		    return SendMessage::sendErrorMsg(Yii::t('teacher', 'Cut Fail'));
		}
	}

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>