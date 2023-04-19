<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\Group;
use teacher\modules\v1\models\SendMessage;
use common\models\Format;
use teacher\modules\v1\models\Picture;
use common\models\Curl;
use components\Upload;
use yii\data\Pagination;
use teacher\modules\v1\models\Campus;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Video;
use teacher\modules\v1\models\CourseMaterialInfo;
use yii\base\ErrorException;
use teacher\modules\v1\models\Errors;

class CourseMaterialController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\CourseMaterial';
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

		return Picture::findAll(['id'=>$list]);
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

    	$data = Yii::$app->getRequest()->getBodyParams();
    	$group_id  = $data['group_id'];
    	$source    = $data['source'];
    	$image_id  = $data['image_id'];
    	$admin_id  = $data['admin_id'];
    	$model = new Picture();
    	$group = Group::findOne($group_id);

    	$modelClass = $this->modelClass;
		$create_id  = $modelClass::findOne($group['course_material_id'])['admin_id'];

		if(!empty($admin_id)) {

			if($create_id != $admin_id) {

				return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
			}

		}

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
			            $picture->category_id = $data->category_id;
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
	            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
	            $image = Upload::pic_upload($_FILES, $studio, 'picture', 'image');
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
    	$admin_id  = $data['admin_id'];

    	$group    =  Group::findOne($group_id);

    	$modelClass = $this->modelClass;
		$create_id  = $modelClass::findOne($group['course_material_id'])['admin_id'];
		
		if(!empty($admin_id)) {

			if($create_id != $admin_id) {

				return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
			}

		}

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

    public function actionSearch($admin_id,$search_admin_id='',$time='',$category_id='',$page=0,$limit=5) {
    	$modelClass = $this->modelClass;
    	$time = strtotime($time);

    	$admin_ids  =  array_column(Admin::getVisua($admin_id),'admin_id');


    	$course_material_ids = CourseMaterialInfo::getAll($admin_ids);

    	if(!empty($category_id) || !empty($search_admin_id)) {
    		$course_material_ids =  CourseMaterialInfo::GetSearchIds($course_material_ids,$search_admin_id,$category_id);
    	}
		
       $data = $modelClass::find()
    				->where(['id'=>$course_material_ids,'status'=>$modelClass::STATUS_ACTIVE])
    				->andFilterwhere(['>=','created_at',$time])
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
			//生成教案图片视频默认分组

			// $Group = new Group();

			// $types = [Group::TYPE_PICTURE,Group::TYPE_VIDEO];

			// foreach ($types as $key => $value) {
				
			// 	$model = clone $Group;
			// 	$model->type = $value;
			// 	if($value == Group::TYPE_PICTURE){
			// 		$model->name = Yii::t('teacher','Must Picture');
			// 	}else{
			// 		$model->name = Yii::t('teacher','Must Video');
			// 	}
			// 	$model->course_material_id = $CourseMaterialId;

			// 	if(!$model->save()) {
			// 		throw new ErrorException(Errors::getInfo($model->getErrors()));
			// 	}
			// }

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

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>