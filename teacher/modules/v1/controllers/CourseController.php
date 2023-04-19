<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use teacher\modules\v1\models\SendMessage;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\ClassPeriod;	
use teacher\modules\v1\models\Course;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\SimpleCourse;
use teacher\modules\v1\models\CourseMaterial;
use teacher\modules\v1\models\Group;
use teacher\modules\v1\models\CourseMaterialInfo;
use teacher\modules\v1\models\Tool;
use teacher\modules\v1\models\CourseCutInfo;
use yii\base\ErrorException;
use teacher\modules\v1\models\Errors;

class CourseController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\Course';
  /**
	 *[获取课程]
     *
     *@param inter [admin_id] 用户id
     *@return 
	*/
	public function actionIndex($class_id,$started_at,$ended_at,$admin_id='',$type = 1)
	{
		error_reporting(0);
        $modelClass = $this->modelClass;

        $model  = new SimpleCourse();

        if($type == 3) {
        	$course = $this->modelClass;
        }else{
        	$course = new SimpleCourse();
        }
        $ids = $model::getDateType($started_at,$ended_at,$class_id);

		//获取上课时间列表
		$class_period_info =  ClassPeriod::getInfo($class_id);
		$class_period_ids  = array_keys($class_period_info);

		$array = array();
		$course_list = $course::findAll(['id'=>$ids]);

		$started_at = strtotime($started_at);
		$ended_at   = strtotime($ended_at);
		//时间段间隔的天数

		$days = Tool::DiffDays($started_at,$ended_at);
		//剪切详情
		$CourseCutInfo =  CourseCutInfo::getAll($ids);

		if($course_list){
			for ($i=0; $i <= $days; $i++) { 
				$time =  $started_at+$i*24*3600;
				$day = date("Y/m/d",$time);
	        	foreach ($course_list as $key => $value) {
	        		if($value->started_at <= $time  && $time <= $value->ended_at)
	        		{
	        			$array[$day][$key] = $value;
	        		}else{
	        			$array[$day][$key] = (object)array();
	        		}
	        	}
			}
			foreach ($array as $key => $value) {
				unset($array[$key]);
				$array[$key]['time']   = $key;
				$array[$key]['course'] = $value;
				$array[$key]['is_my_course']   = 0;
				foreach ($value as $k => $v) {
					if($v->admin_id == $admin_id) {
						$array[$key]['is_my_course']   = 1;
						break;
					}
				}
			}

			foreach ($array as $key => $value) {
				foreach ($value as $mix_key => $mix_value) {
					if($mix_key == 'course') {
						foreach ($mix_value as $k => $v) {
							if(!($v->id)){
								unset($array[$key][$mix_key][$k]);
							}
						}
					}
				}
			}

			foreach ($array as $key => $value) {
				foreach ($value as $mix_key => $mix_value) {
					if($mix_key == 'course') {
						if(empty($mix_value)) {
							foreach ($class_period_ids as  $class_period_key=>$class_period_value) {
								$array[$key][$mix_key][$class_period_key] = (Object)array('class_period_id'=>$class_period_info[$class_period_value]);
							}									
						}else{
							unset($mix_value[$mix_key]);
							$course_day = array();
							foreach ($mix_value as $course_key => $course_value) {
								foreach ($class_period_ids as  $class_period_key=>$class_period_value) {
									$flag = true;
									foreach ($CourseCutInfo as $course_cut_key => $course_cut_value) {
										if($course_value->id == $course_cut_value->course_id && $course_cut_value->class_period_id == $class_period_value && strtotime($value['time']) == strtotime($course_cut_value['time'])) {
											$flag = false;
										}
									}
									if($course_value->class_period_id == $class_period_value && $flag)
									{	
										$course_day[$class_period_key] = $course_value;
									}	


								}
							}
							for ($i=0; $i < count($class_period_ids); $i++) { 
								if(!isset($course_day[$i])) {
									$course_day[$i] = (Object)array('class_period_id'=>$class_period_info[(current($class_period_info)['class_period_id']+$i)]);
								}else{
									$course_day[$i] = $course_day[$i];
								}
							}
							ksort($course_day);
							$array[$key][$mix_key] = array_values($course_day);
						}
					}
				}
			}
		}else{
			for ($i=0; $i <= $days; $i++) { 
				$time =  $started_at+$i*24*3600;
				$day = date("Y/m/d",$time);
	        	$array[$day] = (object)array();
			}

			foreach ($array as $key => $value) {
				unset($array[$key]);
				$array[$key]['time']   = $key;
				$array[$key]['course'] = $value;
			}

			foreach ($array as $key => $value) {
				foreach ($value as $mix_key => $mix_value) {
					if($mix_key == 'course') {
						foreach ($class_period_ids as  $class_period_key=>$class_period_value) {
							$array[$key][$mix_key]->$class_period_key = (Object)array('class_period_id'=>$class_period_info[$class_period_value]);
						}									
						
					}
				}
			}
		}
		$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
		return array_values($array);
	}
  /**
	 *[获取课程]
     *
     *@param inter [admin_id] 用户id
     *@return 
	*/
	public function actionGetList($class_id,$started_at,$ended_at,$type = 1)
	{
		error_reporting(0);
        $modelClass = $this->modelClass;

        $model  = new SimpleCourse();

        if($type == 3) {
        	$course = $this->modelClass;
        }else{
        	$course = new SimpleCourse();
        }
        $ids = $model::getDateType($started_at,$ended_at,$class_id);

		//获取上课时间列表
		$class_period_info =  ClassPeriod::getInfo($class_id);
		$class_period_ids  = array_keys($class_period_info);

		$array = array();
		$course_list = $course::findAll(['id'=>$ids]);

		$started_at = strtotime($started_at);
		$ended_at   = strtotime($ended_at);
		//时间段间隔的天数

		$days = Tool::DiffDays($started_at,$ended_at);
		//剪切详情
		$CourseCutInfo =  CourseCutInfo::getAll($ids);

		if($course_list){
			for ($i=0; $i <= $days; $i++) { 
				$time =  $started_at+$i*24*3600;
				$day = date("Y/m/d",$time);
	        	foreach ($course_list as $key => $value) {
	        		if($value->started_at <= $time  && $time <= $value->ended_at)
	        		{
	        			$array[$day][$key] = $value;
	        		}else{
	        			$array[$day][$key] = (object)array();
	        		}
	        	}
			}
			foreach ($array as $key => $value) {
				unset($array[$key]);
				$array[$key]['time']   = $key;
				$array[$key]['course'] = $value;
			}

			foreach ($array as $key => $value) {
				foreach ($value as $mix_key => $mix_value) {
					if($mix_key == 'course') {
						foreach ($mix_value as $k => $v) {
							if(!($v->id)){
								unset($array[$key][$mix_key][$k]);
							}
						}
					}
				}
			}

			foreach ($array as $key => $value) {
				foreach ($value as $mix_key => $mix_value) {
					if($mix_key == 'course') {
						if(empty($mix_value)) {
							foreach ($class_period_ids as  $class_period_key=>$class_period_value) {
								$array[$key][$mix_key][$class_period_key] = (Object)array('class_period_id'=>$class_period_info[$class_period_value]);
							}									
						}else{
							unset($mix_value[$mix_key]);
							$course_day = array();
							foreach ($mix_value as $course_key => $course_value) {
								foreach ($class_period_ids as  $class_period_key=>$class_period_value) {
									$flag = true;
									foreach ($CourseCutInfo as $course_cut_key => $course_cut_value) {
										if($course_value->id == $course_cut_value->course_id && $course_cut_value->class_period_id == $class_period_value && strtotime($value['time']) == strtotime($course_cut_value['time'])) {
											$flag = false;
										}
									}
									if($course_value->class_period_id == $class_period_value && $flag)
									{	
										$course_day[$class_period_key] = $course_value;
									}	


								}
							}
							for ($i=0; $i < count($class_period_ids); $i++) { 
								if(!isset($course_day[$i])) {
									$course_day[$i] = (Object)array('class_period_id'=>$class_period_info[(current($class_period_info)['class_period_id']+$i)]);
								}else{
									$course_day[$i] = $course_day[$i];
								}
							}
							ksort($course_day);
							$array[$key][$mix_key] = array_values($course_day);
						}
					}
				}
			}
		}else{
			for ($i=0; $i <= $days; $i++) { 
				$time =  $started_at+$i*24*3600;
				$day = date("Y/m/d",$time);
	        	$array[$day] = (object)array();
			}

			foreach ($array as $key => $value) {
				unset($array[$key]);
				$array[$key]['time']   = $key;
				$array[$key]['course'] = $value;
			}

			foreach ($array as $key => $value) {
				foreach ($value as $mix_key => $mix_value) {
					if($mix_key == 'course') {
						foreach ($class_period_ids as  $class_period_key=>$class_period_value) {
							$array[$key][$mix_key]->$class_period_key = (Object)array('class_period_id'=>$class_period_info[$class_period_value]);
						}									
						
					}
				}
			}
		}
		$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
		return array_values($array);
	}


	//剪切  or 删除
	public function actionCut($course_id,$class_period_id,$time,$type,$admin_id='') {

		$modelClass = $this->modelClass;

		$create_id  = $modelClass::findOne($course_id)['admin_id'];

		if(!empty($admin_id)) {

			if($create_id != $admin_id) {

				return SendMessage::sendErrorMsg(Yii::t('teacher','NOT Edit'));
			}
		}

		$CourseCutInfo = new CourseCutInfo();

		if($type == CourseCutInfo::HANDEL_DEL) {

			$CourseCutInfo->status = CourseCutInfo::STATUS_ACTIVE;
		}

		$CourseCutInfo->load(Yii::$app->getRequest()->get(),'');

		if($CourseCutInfo->load(Yii::$app->getRequest()->get(),'') && $CourseCutInfo->save()) {
			if($type == CourseCutInfo::HANDEL_DEL) {
				return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));
			}else{
				$_GET['message'] = Yii::t('teacher','Cut Success');

				return Yii::$app->db->getLastInsertId();
			}	
		}else{
			return SendMessage::sendVerifyErrorMsg($CourseCutInfo,Yii::t('teacher','Handle Fail'));
		}
	}

	//粘贴
	public function actionPaste($course_id,$class_period_id,$time,$admin_id,$course_cut_id='',$type)
	{
		error_reporting(0);
		$modelClass = $this->modelClass;
		$course = $this->findModel($course_id);
		$model = new Course();
		$model->class_period_id       = $class_period_id;
		$model->class_emphasis        = $course->class_emphasis;
	   	$model->class_id  			  = $course->class_id;
	    $model->category_id 		  = $course->category_id;
    	$model->instructor 			  = $admin_id;
    	$model->admin_id 			  = $admin_id;  
	    $model->instruction_method_id = $course->instruction_method_id;
	    $model->course_material_id    = $course->course_material_id;
	    $model->class_content   	  = $course->class_content;
	    $model->class_emphasis  	  = $course->class_emphasis;
	    $model->started_at			  = strtotime($time);
	    $model->ended_at	          = strtotime($time);

	    if($type == CourseCutInfo::HANDEL_CUT){
		    $CourseMaterialInfo = new CourseMaterialInfo();

		    $CourseMaterialInfo->class_content = $course->class_content;

		    $CourseMaterialInfo->admin_id = $admin_id;

		    $CourseMaterialInfo->instruction_method_id = $course->instruction_method_id;

		    $CourseMaterialInfo->category_id = $course->category_id;

		    $CourseMaterialInfo->course_material_id = $course->category_id;

		    $CourseCutInfo = CourseCutInfo::findOne($course_cut_id);

		    $CourseCutInfo->status = CourseCutInfo::STATUS_ACTIVE;
		}

	    $connect = Yii::$app->db->beginTransaction();
	    try {

	    	if($type == CourseCutInfo::HANDEL_CUT){
		    	if(!$CourseMaterialInfo->save()) {

		    		throw new ErrorException(Errors::getInfo($CourseMaterialInfo->getErrors()));
		    	}

		    	if(!$CourseCutInfo->save()) {

		    		throw new ErrorException(Errors::getInfo($CourseCutInfo->getErrors()));
		    	}
		    }
	    	if(!$model->save()) {

	    		throw new ErrorException(Errors::getInfo($model->getErrors()));
	    	}else{

	    		$CourseId = Yii::$app->db->getLastInsertId();
	    	}

	    	$connect->commit();
	    	$_GET['message'] = Yii::t('teacher','Handle Success');

	    	return Course::findOne($CourseId);
	    } catch (ErrorException $e) {
	    	return SendMessage::sendErrorMsg($e->getMessage());
	    }
	}

	/**
	 *[获取课程最小时间]
     *
     *@param inter [admin_id] 用户id
     *@return 
	*/
	public function actionGetMin()
	{
		$modelClass = $this->modelClass;

		$_GET['message'] = Yii::t('teacher', 'Sucessfully Get Date');
		$minTime = date('Y-m-d',$modelClass::find()->select('started_at')->where(['>','started_at',0])->min('started_at'));
		
		return $minTime;
	}

	/**
	 *[创建课程]
	*/
	public function actionCreate()
	{
		
		$connect = Yii::$app->db->beginTransaction();

		$data  = Yii::$app->getRequest()->getBodyParams();

		$class_period_id = $data['class_period_id']; 

		$time = $data['time'];

		$class_id = $data['class_id'];

		$category_id = $data['category_id'];

		$instructor  = $data['admin_id'];

		$admin_id    = $data['admin_id'];

		$instruction_method_id = $data['instruction_method_id'];

		$course_material_name = $data['course_material_name'];

		$course_material_depict = $data['depict'];

		//教学内容
		$class_content = $data['class_content'];

		try {
				$CourseMaterial = new CourseMaterial();

				$CourseMaterial->admin_id = $admin_id;

				$CourseMaterial->name = $course_material_name;

				$CourseMaterial->description = $course_material_depict;

				$CourseMaterial->save(false);

				$CourseMaterialId =  Yii::$app->db->getLastInsertID();

				$CourseMaterialInfo = new CourseMaterialInfo();

				$CourseMaterialInfo->course_material_id = $CourseMaterialId;

				$CourseMaterialInfo->instruction_method_id = $instruction_method_id;

				$CourseMaterialInfo->class_content = $class_content;

				$CourseMaterialInfo->category_id = $category_id;

				$CourseMaterialInfo->admin_id    = $admin_id;

				if(!$CourseMaterialInfo->save()) {
					throw new ErrorException(Errors::getInfo($CourseMaterialInfo->getErrors()));
				}

				$time = strtotime($time);

				$Course = new Course();

				$Course->started_at = $time;

				$Course->ended_at   = $time;

				$Course->class_period_id = $class_period_id;

				$Course->admin_id = $admin_id;

				$Course->instructor = $admin_id;

				$Course->instruction_method_id = $instruction_method_id;

				$Course->category_id = $category_id;

				$Course->class_id   = $class_id;

				$Course->class_content = $class_content;

				$Course->status = Course::STATUS_DELETED;

				$Course->course_material_id = $CourseMaterialId;
				
				$Course->save(false);

				$CourseId = Yii::$app->db->getLastInsertID();

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
				// 	$model->save();
				// }
			$connect->commit();

			$_GET['message'] = Yii::t('teacher','Make Success');

			return [
				'course_id' => $CourseId,
				'course_material_id' => $CourseMaterialId,
				'admin_id'           => $admin_id,
			];
		} catch (Exception $e) {
		    $connect->rollBack();
		    return SendMessage::sendErrorMsg(Yii::t('teacher', 'Make Fail'));
		}	
	}

	/**
	*[创建课件完成]
	*@param inter course_id 课程id
	*
	*/
	public function actionComplete($course_id) {

		$model = $this->findModel($course_id);

		$model->status = Course::STATUS_ACTIVE;

		if($model->save()) {
		   return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Make Success'));
		}else{
		   return SendMessage::sendErrorMsg(Yii::t('teacher', 'Make Fail'));
		}
	}

	//选择课件
	public function actionChooseMaterial ($course_material_id,$time,$class_period_id,$class_id,$admin_id) {

		$info = CourseMaterialInfo::getInfo($course_material_id);

		$time = strtotime($time);

		$Course = new Course();

		$Course->setScenario('choose');

		$Course->course_material_id = $course_material_id;

		$Course->class_period_id    = $class_period_id;

		$Course->admin_id = $admin_id;

		$Course->instructor = $admin_id;

		$Course->started_at = $time;

		$Course->ended_at   = $time;

		$Course->instruction_method_id = $info['instruction_method_id'];

		$Course->category_id = $info['category_id'];

		$Course->class_id   = $class_id;

		$Course->class_content = $info['class_content'];

		if($Course->save()) {
			return SendMessage::sendSuccessMsg(Yii::t('teacher','Make Success'));
		}else{
			return SendMessage::sendVerifyErrorMsg($Course,Yii::t('teacher','Make Fail'));
		}
	}

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>