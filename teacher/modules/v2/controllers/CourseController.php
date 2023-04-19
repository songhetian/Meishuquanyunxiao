<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\Tool;
use teacher\modules\v2\models\Sign;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v1\models\Group;
use teacher\modules\v2\models\Course;
use teacher\modules\v1\models\Errors;
use teacher\modules\v2\models\Classes;
use teacher\modules\v1\models\ClassPeriod;	
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\SimpleCourse;
use teacher\modules\v2\models\CourseMaterial;
use teacher\modules\v1\models\CourseCutInfo;
use teacher\modules\v1\models\CourseMaterialInfo;

class CourseController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Course';
  /**
	 *[获取课程]
     *
     *@param inter [admin_id] 用户id
     *@return 
	*/
	public function actionIndex($class_id='',$started_at,$ended_at,$admin_id = '',$type = 1)
	{
		error_reporting(0);
        $modelClass = $this->modelClass;

        $model  = new SimpleCourse();

        if($type == 3) {
        	$course = $this->modelClass;
        }else{
        	$course = new SimpleCourse();
        }
        
       if($this->user_role == 10){
        	$course::$user_id = $this->user_id;
        }
        $ids = $model::getDateType($started_at,$ended_at,$class_id,$this->user_role,$this->user_id,$this->studio_id);
    
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
	public function actionIndexTest($class_id='',$started_at,$ended_at,$admin_id = '',$type = 1)
	{
		error_reporting(0);
        $modelClass = $this->modelClass;

        $model  = new SimpleCourse();

        if($type == 3) {
        	$course = $this->modelClass;
        	$course::$is_view = 2;
        	if($this->user_role == 10){
        		$course::$user_id = $this->user_id;
        	}
        }else{
        	$course = new SimpleCourse();
        	$course::$is_view = 2;
        	if($this->user_role == 10){
        		$course::$user_id = $this->user_id;
        	}
        }

        $ids = $model::getDateType($started_at,$ended_at,$class_id,$this->user_role,$this->user_id,$this->studio_id);

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

			$model  = $modelClass::findOne($course_id);

			$CourseCutInfo->status = CourseCutInfo::STATUS_DELETED;

			$model->status = CourseCutInfo::STATUS_DELETED;
		}

		$CourseCutInfo->load(Yii::$app->getRequest()->get(),'');

		if($CourseCutInfo->load(Yii::$app->getRequest()->get(),'') && $CourseCutInfo->save()) {
			if($type == CourseCutInfo::HANDEL_DEL) {

				if($model->save()) {
					return SendMessage::sendSuccessMsg(Yii::t('teacher','Delete Success'));
				}
			}else{
				$_GET['message'] = Yii::t('teacher','Cut Success');

				return Yii::$app->db->getLastInsertId();
			}	
		}else{
			return SendMessage::sendVerifyErrorMsg($CourseCutInfo,Yii::t('teacher','Handle Fail'));
		}
	}

	//粘贴
	public function actionPaste($course_id,$class_period_id,$time,$admin_id,$course_cut_id='',$class_id='',$type)
	{
		error_reporting(0);
		$modelClass = $this->modelClass;
		$course = $this->findModel($course_id);
		$model = new Course();
		$model->class_period_id       = $class_period_id;
		$model->class_emphasis        = $course->class_emphasis;
		if($class_id){
			$model->class_id  			  = $class_id;
		}else{
			$model->class_id  			  = $course->class_id;
		}
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

		$admin_id    = $data['admin_id'];

		//收费课程验证
		$ClassType = Classes::findOne($class_id)->type;
		
		if(in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $ClassType == 2){

			$TeacherList =	Classes::getEditorList($class_id);

			$item_name   =  Admin::findOne($this->user_id)->auths->item_name;
			
			$pid         = substr($item_name,-3);


			$flag = (in_array($pid,Yii::$app->params['Shenfen']) || in_array($this->user_id,$TeacherList)) ? true : false;

			if(!$flag) {
				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}
		}else{
			$model = Admin::findOne($this->user_id);

			$flag  = ($model->is_create == 1) ? true : false;

			if(!$flag) {
				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}
		}
		
		$category_id = $data['category_id'];

		$instructor  = $data['admin_id'];

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
				
				$time                          = strtotime($time);
				
				$Course                        = new Course();

				$Course->started_at            = $time;

				$Course->ended_at              = $time;
				
				$Course->class_period_id       = $class_period_id;
				
				$Course->admin_id              = $admin_id;
				
				$Course->instructor            = $admin_id;
				
				$Course->instruction_method_id = $instruction_method_id;
				
				$Course->category_id           = $category_id;
				
				$Course->class_id              = $class_id;
				
				$Course->class_content         = $class_content;
				
				$Course->status                = Course::STATUS_DELETED;
				
				$Course->course_material_id    = $CourseMaterialId;
				
				$Course->save(false);
				
				$CourseId                      = Yii::$app->db->getLastInsertID();


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

		$model    = $this->findModel($course_id);
		
		// $class_id = $model->class_id;
		// //收费课程验证
		// $ClassType = Classes::findOne($class_id)->type;
		
		// if(in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $ClassType == 2){

		// 	$TeacherList =	Classes::getEditorList($class_id);

		// 	$item_name   =  Admin::findOne($this->user_id)->auths->item_name;
			
		// 	$pid         = substr($item_name,-3);


		// 	$flag = (in_array($pid,Yii::$app->params['Shenfen']) || in_array($this->user_id,$TeacherList)) ? true : false;

		// 	if(!$flag) {
		// 		return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
		// 	}
		// }else{
		// 	$Teacher = Admin::findOne($this->user_id);

		// 	$flag  = ($Teacher->is_create == 1) ? true : false;

		// 	if(!$flag) {
		// 		return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
		// 	}
		// }

		$model->status = Course::STATUS_ACTIVE;

		if($model->save()) {
		   return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Make Success'));
		}else{
		   return SendMessage::sendErrorMsg(Yii::t('teacher', 'Make Fail'));
		}
	}

	//选择课件
	public function actionChooseMaterial ($course_material_id,$time,$class_period_id,$class_id,$admin_id) {

		//收费课程验证
		$ClassType = Classes::findOne($class_id)->type;
		
		if(in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $ClassType == 2){
			
			$TeacherList =	Classes::getEditorList($class_id);

			$item_name   =  Admin::findOne($this->user_id)->auths->item_name;
			
			$pid         = substr($item_name,-3);


			$flag = (in_array($pid,Yii::$app->params['Shenfen']) || in_array($this->user_id,$TeacherList)) ? true : false;

			if(!$flag) {
				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}
		}
		
		$info                       = CourseMaterialInfo::getInfo($course_material_id);
		
		$Course                     = new Course();
		
		#$Course->setScenario('choose');
		
		$Course->course_material_id = $course_material_id;
		
		$Course->class_period_id    = $class_period_id;
		
		$Course->admin_id           = $admin_id;
		
		$Course->instructor         = $admin_id;
		
		$Course->started_at         = strtotime($time);
		
		$Course->ended_at           = strtotime($time);

		if($info){

			$Course->instruction_method_id = $info['instruction_method_id'];

			$Course->category_id = $info['category_id'];

			$Course->class_content = $info['class_content'];
		}

		$Course->class_id   = $class_id;

		if($Course->save()) {
			return SendMessage::sendSuccessMsg(Yii::t('teacher','Make Success'));
		}else{
			return SendMessage::sendVerifyErrorMsg($Course,Yii::t('teacher','Make Fail'));
		}
	}
	/**
	 * [actionGeIndest 获取课程列表]
	 * @开发者    tianhesong
	 * @创建时间   2020-04-06T16:39:56+0800
	 * @param  [type]                   $time 	  [搜索时间]
	 * @param  [type]                   $class_id [班级id]
	 * @param  [type]                   $class_id [班级id]
	 * @return [type]                         [description]
	 */
	public function actionGetCourseList($time,$class_id,$turn = 'before',$page,$limit = 5) {
		
		$QueryTime = strtotime(date("Y-m-d",strtotime("+1 days",strtotime($time))));
		
		$query = SimpleCourse::find()
						  ->select("id,started_at,class_period_id")
						  ->where(['status' => 10,'class_id'=>$class_id]);

		if($turn == 'before') {
			$query->andWhere(['<','started_at',$QueryTime]);
			$list = $query->offset($page*$limit)
							  ->limit($limit)
							  ->orderBy("started_at DESC,class_period_id ASC")
							  ->asArray()
							  ->all();
		}elseif($turn == 'after') {
			$query->andWhere(['>','started_at',$QueryTime]);
			$list = $query->offset($page*$limit)
							  ->limit($limit)
							  ->orderBy("started_at ASC,class_period_id ASC")
							  ->asArray()
							  ->all();
		}

		$TimeList  = ArrayHelper::getColumn($list, function ($element) {
		    return date("Y-m-d",$element['started_at']);
		});

		$Times    = array_count_values($TimeList);

		$CountTimes = array();

		foreach ($Times as $key => $value) {
			if($value > 1 ) {
				$CountTimes[] = $key;
			}
		}
		
		$result = ArrayHelper::getColumn($list, function ($element) {
		    return array('time'=>date("Y-m-d",$element['started_at']),'id'=>$element['id']);
		});
		$ListId = array();
		foreach ($result as $key => $value) {

			if(in_array($value['time'],$CountTimes)) {

				$ListId[$value['time']][] = $value['id'];
			}else{
				$ListId[$value['time']][] = $value['id'];
			}
		}

		$FinalResult = array();

		foreach ($ListId as $key => $value) {
			$FinalResult[] = array(
							'time' => $key,
							'value'=>SimpleCourse::findAll($value)
						);
								 
		}
		$_GET['message'] = "获取信息成功";
		return $FinalResult;
	}
	/**
	 * [actionCommute 签到]
	 * @开发者    tianhesong
	 * @创建时间   2020-04-07T02:14:00+0800
	 * @param  [type]                   $course_id [description]
	 * @return [type]                              [description]
	 */
	public function actionCommute($course_id,$class_period_id) {
		
		if($this->user_role != 20) {
			return SendMessage::sendErrorMsg('只有学生身份有此功能');	
		}

		switch (Sign::Check($course_id,$class_period_id,$this->user_id)) {
			case 1:
				return SendMessage::sendErrorMsg('未到开课时间');
				break;
			case 2:
				return SendMessage::sendErrorMsg('课程已经结束');
				break;
			case 3:
				return SendMessage::sendErrorMsg('已签到!');
				break;
		}

		$model = new Sign();

		$model->user_id         = $this->user_id;
		$model->course_id       = $course_id;
		#$model->class_period_id = $class_period_id;
		$model->class_id        = User::findOne($this->user_id)->class_id;

		if($model->save()) {
			return SendMessage::sendSuccessMsg('签到成功!');
		}else{
			return SendMessage::sendVerifyErrorMsg($model,'签到失败');
		}	
	}

	/**
	 * [actionCommuteCensus 签到统计]
	 * @开发者    tianhesong
	 * @创建时间   2020-04-07T02:46:35+0800
	 * @param  [type]                   $course_id       [description]
	 * @param  [type]                   $class_period_id [description]
	 * @return [type]                                    [description]
	 */
	public function actionCommuteCensus($course_id,$class_id) {

		$list =  \common\models\User::find()
		->select('id')
		->where(['class_id'=>$class_id,'status'=>10])
		->all();

		$Already = Sign::find()
		->select('user_id,created_at')
		->where([
			'class_id'=>$class_id,
			'status'=>10,
			'course_id'=>$course_id
		])
		->all();
	
		$AllList=  ArrayHelper::getColumn($list,'id');

		$AlreadyList = ArrayHelper::getColumn($Already,'user_id');
	
		foreach ($AllList as $key => $value) {
			if(in_array($value, $AlreadyList)) {
				unset($AllList[$key]);
			}
		}

		$NotList = \common\models\User::find()
 						->select('id,name')
						->where(['id'=>$AllList])
						->andWhere(['NOT',['name'=>NULL]])
						->andWhere(['NOT',['name'=>'']])
						->asArray()
						->all();

		$NotSignIn = array();
		foreach ($NotList as $key => $value) {
			$NotSignIn[$key]['user_id']    = $value['id'];
			$NotSignIn[$key]['created_at'] = NULL;
			$NotSignIn[$key]['name']       = $value['name'];
			$NotSignIn[$key]['identifier'] = 'student'.$value['id'];
		}

		$_GET['message'] = '获取信息成功';

		return  array(
			'SignIn' => $Already,
			'NotSignIn' => $NotSignIn
		);
		
	}

	//课程修改
	public function actionUpdateImage($course_id,$image_id){


		$model             =  $this->findModel($course_id);
		
		$model->prew_image = $image_id;

		if($model->save()) {
			return SendMessage::sendSuccessMsg('修改成功!');
		}else{
			return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Make Fail'));
		}

	}
	
	//获取课程详情
	public function actionGetOne($course_id) {

		$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
		return $this->findModel($course_id);
	}

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>