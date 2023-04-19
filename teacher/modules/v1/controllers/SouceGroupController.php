<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\Format;
use common\models\Curl;
use components\Upload;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Picture;
use teacher\modules\v1\models\Video;
use teacher\modules\v1\models\SendMessage;
use teacher\modules\v1\models\Campus;
use teacher\modules\v1\models\SouceGroup;

class SouceGroupController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\SouceGroup';

    /**
     * [actionIndex 获取教案分组创建]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $course_material_id [教案ID]
     * @param     integer       $type               [类型] 10为图片 20为视频
     */
	public function actionCreate()
	{
		$model = new SouceGroup();

		$name = Yii::$app->request->post('name');
		

		if(in_array($name, $model::Exists(Yii::$app->request->post('admin_id')))) {

			 return SendMessage::sendErrorMsg(Yii::t('teacher', 'Gropu Exixts'));
		}
	
		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save())
		{
			$_GET['message'] = Yii::t('teacher','Create Group Success');
			return $model;
		}else{
			return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Create Group Fail'));
		}
	}
    /**
     * [actionIndex 获取分组]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $group_id [分组ID]
     * @param     integer       $group_id [资源类型ID] 10为图片 20为视频
     */
	public function actionGetGroup($admin_id,$type)
	{
		$modelClass = $this->modelClass;

		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		$query = $modelClass::find()
							->where(['admin_id'=>$admin_id,'status'=>$modelClass::STATUS_ACTIVE,'type'=>$type])
							->orderBy('is_main DESC');
		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
            	'pagesize' => -1,
        	]
		]);
	}

    /**
     * [actionIndex 获取教案分组展示]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $group_id [分组ID]
     * @param     integer       $group_id [资源类型ID] 10为图片 20为视频
     */
	public function actionSourceView($group_id,$type,$page=0,$limit=50)
	{
		$modelClass = $this->modelClass;

		$list = Format::explodeValue($modelClass::findOne($group_id)->material_library_id);

		$offset = ($page*$limit);

		$list = array_slice($list,$offset,$limit);

		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		switch ($type) {
			case $modelClass::TYPE_PICTURE:
				return Picture::findAll(['id'=>$list]);
				break;
			case $modelClass::TYPE_VIDEO:
				return Video::findAll(['id'=>$list]);
				break;
			default:
				
				break;
		}
	}

    /**
	 *[分组添加图片]
	 *@param group_id 分组id source 来源 1.美术圈图片 2.我的相册 
	 *
	 *
    */
    public function actionAddImage()
    {
    	$modelClass = $this->modelClass;
    	$data = Yii::$app->getRequest()->getBodyParams();
    	$group_id  = $data['group_id'];
    	$admin_id  = $data['admin_id'];
    	$model = new Picture();
    	$group = SouceGroup::findOne($group_id);
        $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
        $image = Upload::pic_upload($_FILES, $studio, 'picture', 'image');
		$group = $modelClass::findOne($group_id);

        $connect = Yii::$app->db->beginTransaction();
        try {
        	foreach ($image as $value) {
        		$picture = clone $model;
        		$picture->image = $value;
        		$picture->admin_id = $admin_id;
        		$picture->save();
        		$group_image[] = Yii::$app->db->getLastInsertId();
        	}
		    if($group->material_library_id){
		    	$group->material_library_id = $group->material_library_id.','.implode(',',$group_image);
		    }else{
		    	$group->material_library_id = implode(',',$group_image);
		    }

		    $group->save();

		    $list = $model::findAll(['id'=>$group_image]);
		    $_GET['message'] = Yii::t('teacher','Add Success');
		    $connect->commit();	
		    return $list;
        				    
		} catch (Exception $e) {
		    $connect->rollBack();
		    return SendMessage::sendErrorMsg(Yii::t('teacher', 'Add Fail'));
		}
	}

    /*
     *[美术视频添加到我的素材库]
     *@param image_id inter 美术圈图片id
     *
    */
    public function actionAddVideo()
    {
        error_reporting(0);
    	$data = Yii::$app->getRequest()->getBodyParams();
    	$group_id  = $data['group_id'];
    	$video_id  = $data['video_id'];
    	$admin_id  = $data['admin_id'];
        $videos = Format::explodeValue($video_id);
		$model = new Video();
		$group = SouceGroup::findOne($group_id);
		$connect = Yii::$app->db->beginTransaction();
        try {
    		foreach ($videos as $key => $value) {
    			$data = current(Curl::metis_file_get_contents(
                	Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$value
            	));
    			$video  =  clone $model;
    			$video  =  Video::AddValue($video,$data,$admin_id);
    			$video->save();
		        $group_video[] = Yii::$app->db->getLastInsertId();
    		}
		    if($group->material_library_id){
		    	$group->material_library_id = $group->material_library_id.','.Format::implodeValue($group_image);
		    }else{
		    	$group->material_library_id = Format::implodeValue($group_image);
		    }

		    $group->save();
		    $connect->commit();
		    $list = Video::findAll(['id'=>$group_video]);
		    $_GET['message'] = Yii::t('teacher','Add Success');
		    return $list;
    	} catch (Exception $e) {
		    $connect->rollBack();
		     return SendMessage::sendErrorMsg(Yii::t('teacher', 'Add Fail'));
		}
    }

	/**
	 *[分组资源删除]
	 *
	 *@param group_id inter 分组id source_id string  删除id 
	 *
	*/
	public function actionSourceDelete($group_id,$source_id)
	{
		$modelClass = $this->modelClass;

		$model = $this->findModel($group_id);

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
	 *【多分组删除】
	 *@param group_id string 分组id
	 *
	 *
	*/
	public function actionGroupMoreDelete($group_id)
	{
		$modelClass = $this->modelClass;

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
	 *【分组删除】
	 *@param group_id inter 分组id
	 *
	 *
	*/
	public function actionGroupDelete($group_id)
	{
		$modelClass = $this->modelClass;

		$model = $modelClass::find()->where(['id'=>$group_id,'status'=>$modelClass::STATUS_ACTIVE])->one();
	
		if($model){
			if($model->is_main == $modelClass::IS_MAIN){
				return SendMessage::sendErrorMsg(Yii::t('teacher','MAIN Not DEL'));
			}else{
				if($model->updateStatus()) {
					return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));
				}else{
					return SendMessage::sendErrorMsg(Yii::t('teacher','Handle Field'));
				}
			}
		}else{
			return SendMessage::sendErrorMsg(Yii::t('teacher','Group Not Exist'));
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