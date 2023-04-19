<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use common\models\CourseMaterial;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\Picture;
use teacher\modules\v1\models\Campus;
use components\Upload;
use teacher\modules\v1\models\SendMessage;
use yii\data\ActiveDataProvider;
use common\models\Curl;
use components\Oss;
use common\models\Format;
use teacher\modules\v1\models\SouceGroup;

class PictureController extends MainController
{

	public $modelClass = 'teacher\modules\v1\models\Picture';

	/**
	 *[获取教案中分组图片]
	 *
	 *@param material_library_id 资源id
	 *
	 *
	*/

   	public function actionIndex($material_library_id)
    {
        $modelClass = $this->modelClass;

        $ids = Format::explodeValue($material_library_id);

        $query = $modelClass::find()->where(['id' => $ids, 'status' => $modelClass::STATUS_ACTIVE]);

        //添加查看次数
        $modelClass::updateAllCounters(['watch_count' => 1], ['id' => $ids]);

        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
	            'pagesize' => -1,
	        ]
        ]);
    }
	/**
	 * [获取美术圈图片]
	 *
	 * @param type[类型10为图片，20为视频。] category_id[一级分类] category_child_id[二级分类]
	 *	      keyword_id[关键字]  publishing_id[出版社] page[当前页] page_limit[显示数量]
	 *@return mixed(array)
	*/

	public function actionGetmeisList($category_id='',$keyword_id='',$publishing_id='',$limit=15,$page=0)
	{
		error_reporting(0);
		$keyword_id = preg_replace("/\s/","",$keyword_id);
        $material = Curl::metis_file_get_contents(
           Yii::$app->params['metis']['Url']['commoditys'].'?category='.$category_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id.'&limit='.$limit.'&page='.$page
        );

		$size = Yii::$app->params['oss']['Size'];
		foreach ($material as $key => $value) {
			if($value->video_url){
				$int  = substr($value->video_url,strripos($value->video_url,'=')+1);
				$info  = Curl::metis_file_get_contents(
               		Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$int
            	);
            	foreach ($info as $info_key => $info_value) {
            		$array = [];
            		$array['video_id'] = $info_value->id;
            		$array['title']    = $info_value->title;
            		$array['charging_option'] = $info_value->charging_option;
            		$array['cc_id'] = $info_value->chapter;
            		$array['preview_image'] = $info_value->preview_image;
            	}
				$material[$key]->video_url = $array;
			}else{
				unset($material[$key]->video_url);
			}
			foreach ($size as $k => $v) {
				$material[$key]->size['image_'.$k] = $v;
			}
		}

		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		return  $material;
	}
	/**
	 *[图片上传]
	 *
	 *
	 *
	*/
	public function actionCreate($admin_id)
	{
		$model = new Picture();
		$model->load(Yii::$app->getRequest()->getBodyParams(),'');
        $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
        $image = Upload::pic_upload($_FILES, $studio, 'picture', 'image');

        $connect = Yii::$app->db->beginTransaction();
        try {
        	foreach ($image as $value) {
        		$picture = clone $model;
        		$picture->image = $value;
        		$picture->save();
        	}
		    $connect->commit();
		    return SendMessage::sendSuccessMsg(Yii::t('teacher','Picture Success'));
		} catch (Exception $e) {
		    $connect->rollBack();
		    return SendMessage::sendVerifyErrorMsg($model, Yii::t('teacher', 'Verify Error'));
		}
	}

	/*
	 *[美术圈图片添加到我的素材库]
	 *@param image_id inter 美术圈图片id
	 *
	*/

	public function actionAddMeis($image_id,$admin_id)
	{
		error_reporting(0);
		$model = new Picture();
        $data = current(Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['commodity'].'?id='.$image_id
        ));

        $model->admin_id  = $admin_id;
        $model->source = Picture::SOURCE_METIS;
        $model->name = ($data->name) ? $data->name : Yii::t('backend', 'Name Is Empty');
        $model->metis_material_id = $data->id;
        $model->publishing_company = $data->publishing_company;
        $model->keyword_id = $data->keyword_id;
        $model->image = $data->image;
        $model->category_id = $data->category_id;
      	$connect = Yii::$app->db->beginTransaction();

		try {
    		$model->save();
    		$group_image = Yii::$app->db->getLastInsertId();

    		$group_id    = SouceGroup::getDefault($admin_id,SouceGroup::TYPE_PICTURE);
    		if(!isset($group_id)) {
            	$group_id = SouceGroup::CreateDefaut($admin_id,SouceGroup::TYPE_PICTURE);
    		}
    		$group = SouceGroup::findOne($group_id);
		    if($group->material_library_id){
		    	$group->material_library_id = $group->material_library_id.','.$group_image;
		    }else{
		    	$group->material_library_id = $group_image;
		    }
		    $group->save();
        	$connect->commit();				      
		    return SendMessage::sendSuccessMsg(Yii::t('teacher','Collection Success'));
		} catch (Exception $e) {
		    $connect->rollBack();
		    return SendMessage::sendErrorMsg(Yii::t('teacher', 'Verify Error'));
		}
	}

	/*
	 *[我的素材库]
	 *
	 *
	*/
	public function actionView($admin_id)
	{
		$modelClass = $this->modelClass;

		$pic_list =  $modelClass::find()->where(['admin_id'=>$admin_id,'status'=>$modelClass::STATUS_ACTIVE])->all();

		foreach ($pic_list as $key => $value) {

			$studio = Campus::findOne(Admin::findOne($value->admin_id)->campus_id)->studio_id;
			if($value->source == $modelClass::SOURCE_LOCAL)
			{
				$pic_list[$key]['image'] =  Oss::getUrl($studio, 'picture', 'image', $value->image);
			}
		}

		$_GET['message'] = Yii::t('teacher','Pictrue List Success');

		return $pic_list;
	}

	/*
	 *[素材库图片删除]
	 *
	 *
	*/
	public function  actionDelte($image_id)
	{
		$model = $this->findModel($image_id);
		$modelClass = $this->modelClass;
		if($model)
		{
			$model->status = $modelClass::STATUS_DELETED;

			if($model->save())
			{
				return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));
			}else{
				return SendMessage::sendVerifyErrorMsg($model, Yii::t('teacher', 'Verify Error'));
			}

		}else{
			return SendMessage::sendErrorMsg(Yii::t('teacher','Pictrue Not Exist'));
		}

	}

	/**
	  *[素材库图片修改]
	  *
	  *@param inter admin_id 老师id
	  *@param inter pic_id   图片id
	  */
	public function  actionPicUpdate($admin_id,$image_id)
	{
		$model = $this->findModel($image_id);
		$studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
		$image = Upload::pic_upload($_FILES['image'], $studio, 'picture', 'image');
        $model->image = $image;
        
        if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save())
        {
        	$_GET['message'] = Yii::t('teacher','Update Success');
        	return $model;
        }else{
        	return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Verify Error'));
        }
	}
	/*
	 *[获取图片oss-size]
	 *
	 *
	 *
	*/
	public function actionGetSize()
	{
		$_GET['message'] = Yii::t('teacher','Sucessfully Get Date');
		return Yii::$app->params['oss']['Size'];
	}

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>