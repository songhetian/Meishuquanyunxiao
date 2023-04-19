<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Curl;
use components\Upload;
use yii\data\Pagination;
use common\models\Format;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\Group;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Admin;
use teacher\modules\v1\models\Video;
use teacher\modules\v1\models\Errors;
use teacher\modules\v2\models\Picture;
use teacher\modules\v1\models\SendMessage;
use teacher\modules\v2\models\CourseMaterial;
use teacher\modules\v1\models\CourseMaterialInfo;
use teacher\modules\v2\models\SimpleCourseMaterial;

class CourseMaterialController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\CourseMaterial';

	public function actionGet($material_id) {

		$modelClass = $this->modelClass;
		$_GET['message'] = "获取成功";

		return  $modelClass::findOne(['id'=>$material_id]);
	}

    /**
     * [actionIndex 创建教案分组创建]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $course_material_id [教案ID]
     * @param     integer       $type               [类型] 10为图片 20为视频
     */
	public function actionCreate()
	{

		$modelClass = $this->modelClass;

		$create_id  = $modelClass::findOne(Yii::$app->request->post('course_material_id'))['admin_id'];

		if(!empty(Yii::$app->request->post('admin_id'))) {

			if($create_id != Yii::$app->request->post('admin_id')) {

				return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
			}

		}
		$model = new Group();
		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save())
		{
			$_GET['message'] = Yii::t('teacher','Create Group Success');
			return $model;
		}else{
			return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Create Group Fail'));
		}
	}

    /**
     * [actionIndex 获取教案分组展示]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $group_id [分组ID]
     * 
     */
	public function actionPicView($group_id)
	{
		$list = Format::explodeValue(Group::findOne($group_id)->material_library_id);

		$_GET['message'] = Yii::t('teacher','Sucessfully List');

		$model = new Picture();

		$model::$user_role = $this->user_role;

		$model::$user_id   = $this->user_id;

		$model::$group_id = $group_id;

		$model::$is_my = 0;

		return $model::findAll(['id'=>$list]);
	}

    /**
     * [actionIndex 获取教案分组视频]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     * @param     integer       $group_id [分组ID]
     * @param     integer       $group_id [资源类型ID] 10为图片 20为视频
     */
	public function actionVideoView($group_id)
	{
		$list = Format::explodeValue(Group::findOne($group_id)->material_library_id);

		$_GET['message'] = Yii::t('teacher','Sucessfully List');

		return Video::findAll(['id'=>$list]);
	}
    /**
     * [actionUpdate 教案修改]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-08
     */
	public function actionUpdate()
	{
    	$modelClass = $this->modelClass;

		$id = Yii::$app->request->post('course_material_id');

		$admin_id = Yii::$app->request->post('admin_id');

		$depict = Yii::$app->request->post('depict');

		$create_id  = $modelClass::findOne($id)['admin_id'];

		if($create_id != $admin_id) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
		}

		$course_material = $modelClass::findOne($id);

		$course_material->description = $depict;

		if($course_material->save(false)){

			return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Course Mater Update Succcess'));
		}else{
			return SendMessage::sendErrorMsg(Yii::t('teacher', $course_material->getErrors()));
		}
	} 
    /**
	 *[分组添加图片]
	 *@param group_id 分组id source 来源 1.美术圈图片 2.我的相册 3.我的素材库
	 *
	 *
    */
    public function actionAddImage()
    {
    	error_reporting(0);
    	$data = Yii::$app->getRequest()->getBodyParams();
    	$group_id  = $data['group_id'];
    	$source    = $data['source'];
    	$image_id  = $data['image_id'];
    	$admin_id  = $this->user_id;
    	$model = new Picture();
    	$group = Group::findOne($group_id);

    	$modelClass = $this->modelClass;
		$create_id  = $modelClass::findOne($group['course_material_id'])['admin_id'];
		
		// if($create_id != $admin_id) {
		// 	return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
		// }

    	switch ($source) {
    		case 1:
				$images = Format::explodeValue($image_id);
				$group_image = array();
	            $connect = Yii::$app->db->beginTransaction();
	            try {
					foreach ($images as $key => $id) {
			            $data = current(Curl::metis_file_get_contents(
			                Yii::$app->params['metis']['Url']['commodity'].'?id='.$id
			            ));
			            $picture = clone $model;
			            $picture->admin_id  = $admin_id;
			            $picture->source = Picture::SOURCE_METIS;
			            $picture->name = ($data->name) ? $data->name : Yii::t('backend', 'Name Is Empty');
			            $picture->metis_material_id = $data->id;
			            $picture->publishing_company = $data->publishing_company;
			            $picture->keyword_id = $data->keyword_id;
			            $picture->image = $data->image;
			            if($data->category_id){
			            	$picture->category_id = $data->category_id;
			            }
			            $picture->save();
			            $group_image[] = Yii::$app->db->getLastInsertId();
		        	}

				    if($group->material_library_id){
				    	$group->material_library_id = $group->material_library_id.','.Format::implodeValue($group_image);
				    }else{
				    	$group->material_library_id = Format::implodeValue($group_image);
				    }

				   	if(!$group->save()) {
				   		throw new ErrorException(Errors::getInfo($group->getErrors()));
				   	}
				    $connect->commit();
				    $list = $model::findAll(['id'=>$group_image]);
				    $_GET['message'] = Yii::t('teacher','Add Success');
				    return $list;
				} catch (Exception $e) {
				    $connect->rollBack();
				    return SendMessage::sendErrorMsg(Yii::t('teacher', 'Add Fail'));
				}
    			break;
    		case 2:
	            $studio = $this->studio_id;
	            $image = Upload::pic_upload($_FILES, $this->studio_id, 'picture', 'image');
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
	            	$connect->commit();
	            	$model::$user_role = $this->user_role;
	            	$model::$user_id = $this->user_id;
	 
				    $list = $model::findAll(['id'=>$group_image]);

				    $_GET['message'] = Yii::t('teacher','Add Success');
				    return $list;
				} catch (Exception $e) {
				    $connect->rollBack();
				    return SendMessage::sendErrorMsg(Yii::t('teacher', 'Add Fail'));
				}
    			break;
    		case 3:
	            $connect = Yii::$app->db->beginTransaction();
	            try {
				    if($group->material_library_id){
				    	$group->material_library_id = $group->material_library_id.','.$image_id;
				    }else{
				    	$group->material_library_id = $image_id;
				    }
				    $group->save();
				    $connect->commit();
				    $list = $model::findAll(['id'=>Format::explodeValue($image_id)]);
				    $_GET['message'] = Yii::t('teacher','Add Success');
				    return $list;
				} catch (Exception $e) {
				    $connect->rollBack();
				     return SendMessage::sendErrorMsg(Yii::t('teacher', 'Add Fail'));
				}
    			break;
    		default:
    			# code...
    			break;
    	}
    }


    /*[教案分组添加视频]
	 *
	 *@param group_id 分组id source 来源 1.美术圈视频 2.我的素材库
	 *
    */
    public function actionAddVideo()
    {
    	error_reporting(0);
    	$data = Yii::$app->getRequest()->getBodyParams();
    	$group_id  = $data['group_id'];
    	$source    = $data['source'];
    	$video_id  = $data['video_id'];
    	$admin_id  = $this->user_id;

    	$group    =  Group::findOne($group_id);

    	$modelClass = $this->modelClass;
		$create_id  = $modelClass::findOne($group['course_material_id'])['admin_id'];
		
		// if($create_id != $admin_id) {

		// 	return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
		// }

    	if($source == 1) {
            $videos = Format::explodeValue($video_id);
    		$model = new Video();
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
			    	$group->material_library_id = $group->material_library_id.','.Format::implodeValue($group_video);
			    }else{
			    	$group->material_library_id = Format::implodeValue($group_video);
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


    	}elseif($source == 2) {
            $connect = Yii::$app->db->beginTransaction();
            try {
			    if($group->material_library_id){
			    	$group->material_library_id = $group->material_library_id.','.$video_id;
			    }else{
			    	$group->material_library_id = $video_id;
			    }
			    $group->save();
			    $connect->commit();
			    $list = Video::findAll(['id'=>Format::explodeValue($video_id)]);
			    $_GET['message'] = Yii::t('teacher','Add Success');
			    return $list;
			} catch (Exception $e) {
			    $connect->rollBack();
			     return SendMessage::sendErrorMsg(Yii::t('teacher', 'Add Fail'));
			}
    	}
    }

    /**
	  *[获取教案列表]
	  *@param admin_id inter 用户id
    */
    public function actionGetList($admin_id,$page=0,$limit=5) {

    	$modelClass = $this->modelClass;

    	$data =  $modelClass::getList($admin_id);

        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $limit]);

        $offset = ($page*$limit);

        $model = $data->offset($offset)->limit($pages->limit)->all();

        $_GET['message'] = Yii::t('teacher','Sucessfully List');

        return $model;
    }

    //教案搜索
    //教案列表
    public function actionSearchTest($admin_id,$search_admin_id='',$name = '' ,$time='',$category_id='',$page=0,$limit=5) {

    	$time = strtotime($time);

    	$admin_ids  =  array_column(Admin::getVisua($admin_id),'admin_id');

    	if($search_admin_id == 0){
    		$search_admin_id  = '';
    	}

    	if($category_id  == 0) {
    		$category_id = '';
    	}
    	$VisuaCategory = Admin::findOne($this->user_id)->category_id;
    	
    	$course_material_ids = CourseMaterialInfo::getAll($admin_ids,$VisuaCategory);

    	if(!empty($category_id) || !empty($search_admin_id)) {
    		$course_material_ids =  CourseMaterialInfo::GetSearchIds($VisuaCategory,$course_material_ids,$search_admin_id,$category_id);
    	}
		
       $data = SimpleCourseMaterial::find()
       			    ->select('id,admin_id,name,description')
    				->where(['id'=>$course_material_ids,'status'=>SimpleCourseMaterial::STATUS_ACTIVE])
    				->andFilterwhere(['>=','created_at',$time])
    				->andFilterwhere(['like','name',$name])
    				->orderBy("updated_at DESC,created_at DESC");
       $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $limit]);

       $offset = ($page*$limit);

       $model = $data->offset($offset)->limit($pages->limit)->all();
       $_GET['message'] = Yii::t('teacher','Sucessfully List');
       return $model;
    }

    //教案搜索
    public function actionSearch($admin_id,$search_admin_id='',$name = '', $time='',$category_id='',$page=0,$limit=5) {
    
    	$time = strtotime($time);

    	$admin_ids  =  array_column(Admin::getVisua($admin_id),'admin_id');

    	if($search_admin_id == 0){
    		$search_admin_id  = '';
    	}

    	if($category_id  == 0) {
    		$category_id = '';
    	}
    	
    	$VisuaCategory = Admin::findOne($this->user_id)->category_id;

    	$course_material_ids = CourseMaterialInfo::getAll($admin_ids,$VisuaCategory);

    	if(!empty($category_id) || !empty($search_admin_id)) {
    		$course_material_ids =  CourseMaterialInfo::GetSearchIds($VisuaCategory,$course_material_ids,$search_admin_id,$category_id);
    	}
		
       $data = CourseMaterial::find()
       			  #  ->select('id,admin_id,name,description')
    				->where(['id'=>$course_material_ids,'status'=>CourseMaterial::STATUS_ACTIVE])
    				->andFilterwhere(['>=','created_at',$time])
    				->andFilterwhere(['like','name',$name])
    				->orderBy("updated_at DESC,created_at DESC");
    				
       $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $limit]);

       $offset = ($page*$limit);

       $model = $data->offset($offset)->limit($pages->limit)->all();
       $_GET['message'] = Yii::t('teacher','Sucessfully List');
       return $model;
    }


  	//创建教案
  	public function actionCreateNoCourse() {

  		$modelClass = $this->modelClass;

  		$data  = Yii::$app->getRequest()->getBodyParams();

  		$connect = Yii::$app->db->beginTransaction();
  		try{
	  		$category_id = $data['category_id'];

	  		$admin_id    = $data['admin_id'];

			$instruction_method_id = $data['instruction_method_id'];

			$course_material_name = $data['course_material_name'];

			$class_content = $data['class_content'];

			$description  = $data['depict'];

			$CourseMaterial    = $modelClass::createModel();

			$CourseMaterial->name = $course_material_name;

			$CourseMaterial->admin_id = $admin_id;

			$CourseMaterial->description = $description;

			if(!$CourseMaterial->save()) {
				throw new ErrorException(Errors::getInfo($CourseMaterial->getErrors()));
			}
			$CourseMaterialId =  Yii::$app->db->getLastInsertId();

			$CourseMaterialInfo = new CourseMaterialInfo();

			$CourseMaterialInfo->course_material_id = $CourseMaterialId;

			$CourseMaterialInfo->instruction_method_id = $instruction_method_id;

			$CourseMaterialInfo->class_content = $class_content;

			$CourseMaterialInfo->category_id = $category_id;

			$CourseMaterialInfo->admin_id    = $admin_id;

			if(!$CourseMaterialInfo->save()) {
				throw new ErrorException(Errors::getInfo($CourseMaterialInfo->getErrors()));
			}
			$connect->commit();
			$_GET['message'] = Yii::t('teacher','Make Success');

			return [
						'course_material_id'=>$CourseMaterialId,
						'admin_id'          => $admin_id,
				   ];

		}catch(ErrorException $e) {
			$connect->rollBack();	
			return SendMessage::sendErrorMsg($e->getMessage());
		}
  	}

  	//我的教案删除
  	public function actionDelete($course_material_id,$admin_id) {

  		$modelClass = $this->modelClass;

  		$model = $modelClass::findOne(['id'=>$course_material_id,'status'=>10]);

  		if(!$model) {
  			return SendMessage::sendErrorMsg("课件不存在或已经删除");
  		}
  		$end_time = SimpleCourseMaterial::getMaxTime($course_material_id);

  		if($end_time > time()){
			return SendMessage::sendErrorMsg("课件存在关联课程,不能删除");
  		}
  		$create_id = $model->admin_id;

  		if($create_id != $admin_id){
  			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
  		}

  		if($model->updateStatus()){
  			return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Course Material Success'));
  		}else{
  			return SendMessage::sendErrorMsg(Yii::t('teacher','Delete Course Material Fail'));
  		}
  	}

  	//修改课件信息
  	public function actionInfoUpdate(){

  		$course_material_id = Yii::$app->request->post('course_material_id');

  		$admin_id = Yii::$app->request->post('admin_id');

  		$depict = Yii::$app->request->post('depict');

  		$model = $this->findModel($course_material_id);

  		$create_id = $model->admin_id;

  		if($create_id != $admin_id){
  			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
  		}

  		$model->load(Yii::$app->getRequest()->getBodyParams(),'');
  		
  		$model->description = $depict;

  		$model_info  =  CourseMaterialInfo::findOne(['course_material_id'=>$course_material_id]);

  		if(!$model_info){
  			$model_info = new CourseMaterialInfo();
  		}
  		$model_info->load(Yii::$app->getRequest()->getBodyParams(),'');

  		$connect = Yii::$app->db->beginTransaction();

	    try {

	    	if(!$model->save()) {

	    		throw new ErrorException(Errors::getInfo($model->getErrors()));
	    	}
	    	if(!$model_info->save()) {

	    		throw new ErrorException(Errors::getInfo($model_info->getErrors()));
	    	}
	    	$connect->commit();
	    
	   		return SendMessage::sendSuccessMsg("修改成功");

	    } catch (ErrorException $e) {
	    	$connect->rollback();
	    	return SendMessage::sendErrorMsg("修改失败");
	    }
  	}

  	public function actionGetIntro($course_material_id) {

  		$model = $this->findModel($course_material_id);
  		$_GET['message'] = Yii::t('teacher','Get Success');
  		return $model['description'];
  	}

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>