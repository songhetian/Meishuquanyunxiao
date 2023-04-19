<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Format;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\ChatFamily;
use teacher\modules\v2\models\ChatClasses;
use teacher\modules\v2\models\ChatAdmin;
use teacher\modules\v2\models\ChatUser;
use teacher\modules\v2\models\MailList;
use teacher\modules\v2\models\SimpleUser;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;
use teacher\modules\v2\models\ChatInfoAdmin;
use teacher\modules\v2\models\ChatInfoFamily;
use teacher\modules\v2\models\ChatInfoUser;
use teacher\modules\v2\models\ChatClassTeacher;
class ChatController extends MainController
{

	public $modelClass = 'teacher\modules\v2\models\ChatAdmin';


	/*检测列表是否更新
	 *
	 *@param
	 *
	 */
	public function actionCheck() {

		if(!$mail = MailList::findOne(['role'=>$this->user_role,'user_id'=>$this->user_id])) {
			$mail =  MailList::Add($this->user_id,$this->user_role,$this->studio_id);
		}

		if($mail->old == $mail->new){
			return SendMessage::sendErrorMsg("没有更新");
		}else{
			return SendMessage::sendSuccessMsg("已经更新");
		}
	}

	//获取聊天列表
	public function actionGetList($name='') {

		$admin_id   = $this->user_id;
		$user_role  = $this->user_role;
		$studio_id  = $this->studio_id;
		$user_type  = $this->user_type;

		if(!$studio_id) {
			 return SendMessage::sendErrorMsg("获取列表失败");
		}


		if(!$mail = MailList::findOne(['role'=>$this->user_role,'user_id'=>$this->user_id])) {

			$mail =  MailList::Add($this->user_id,$this->user_role,$this->studio_id);
		}

		MailList::UpdateOld($admin_id,$user_role);

		switch ($user_type) {
			case 'teacher':
				$teacher_list = [];

				$admin = Admin::findOne($admin_id);

				//散户学生
				$retail_student  = ChatUser::getStudent($studio_id);
				
				$retail_users = $this->GetStudent($retail_student,$name);
				//家长
				$family   = ChatFamily::getAll($studio_id,'',$name);

				if($admin->is_chat) {

					// if($studio_id != 183){
					// 	if($retail_users){
					// 		$teacher_list[] = array('name'=>'新增学生','group_users'=>$retail_users);
					// 	}
					// }

					if($studio_id == 183){

						if($name){
							//散户老师
							$retail_teacher = ChatAdmin::getSanHu($studio_id);

							$item_name =  $admin->auths->item_name;

							$pid = substr($item_name,-3);

							$retail_teacher_list = $this->GetRetailTeachers($retail_teacher,$name);
							
							if(in_array($pid,Yii::$app->params['Shenfen'])) {
								
								$teacher_list[] = array('name'=>'散户老师','group_users'=>$retail_teacher_list);
							}
							
							if($retail_users){
								$teacher_list[] = array('name'=>'新增学生','group_users'=>$retail_users);
							}

							if($family){
								$teacher_list[] = array('name'=>'新增家长','group_users'=>$family);
							}
						}
					}else{
						
						if($retail_users){
							$teacher_list[] = array('name'=>'新增学生','group_users'=>$retail_users);
						}

						if($family){
							$teacher_list[] = array('name'=>'新增家长','group_users'=>$family);
						}	
					}
				}
				
				$teachers  =  $this->GetTeachers(array_column(Admin::getVisuaForChat($admin_id), 'admin_id'),$name);
			
				if($teachers){
					$teacher_list[] = array('name'=>'老师','group_users'=>$teachers);
				}

				$campuses =  Format::explodeValue(Admin::findOne($admin_id)->campus_id);

				//班级
				$classes  =  ChatClasses::getClsses($admin_id,$campuses,$name);

				//获取全部班级
				$teacher_calss = ChatClasses::getTeacherClsses($admin_id,$campuses);
				
				if($name) {
					ChatClasses::$user_name = $name;	
					ChatClassTeacher::$user_name = $name;
				}

				//班级老师

				$class_teachers = ChatClassTeacher::findAll(['id'=>$teacher_calss]);

				foreach ($class_teachers as $key => $value) {
					$teacher_list[] = $value;
				}

				$class_students =  ChatClasses::findAll(['id'=>$classes]);
				foreach ($class_students as $key => $value) {
					$teacher_list[] = $value;
				}

				$_GET['message'] = "获取列表成功";

				return $teacher_list;

				break;
			case 'student':
				$student_list = [];
				//获取学生班级
				$class_id  = ChatUser::findOne($admin_id)->class_id;

				//获取所有老师
				$teachers = ChatAdmin::getTeacherByStudio($studio_id);
				
				$teacher = $this->GetTeachers($teachers,$name);

				if($teacher){
					$student_list[] = array('name'=>'老师','group_users'=>$teacher);
				}
				//家长
				$family    = ChatFamily::getAll($studio_id,$admin_id,$name);

				if($family) {
					$student_list[] = array('name'=>'家长','group_users'=>$family);
				}
				
				$_GET['message'] = "获取列表成功";
				return $student_list;

				break;
			case 'family':

				$family_list = [];
				//学生
				$relation_id = ChatFamily::findOne($admin_id)->relation_id;

				if($relation_id) {
					$student = $this->GetStudent($relation_id,$name);
					if($student){
						$family_list[] = array('name'=>'我的孩子','group_users'=>$student);
					}

					$class_id = SimpleUser::findOne($relation_id)->class_id;
					if($class_id){
						$teachers = ChatClasses::findOne($class_id)->supervisor;

						$teacher = $this->GetTeachers($teachers,$name);

						if($teacher){
							$family_list[] = array('name'=>'老师','group_users'=>$teacher);
						}
					}
				}else{
					$teachers = ChatAdmin::getTeacherByStudio($studio_id);
					$teacher  = $this->GetTeachers($teachers,$name);
					if($teacher){
						$family_list[] = array('name'=>'老师','group_users'=>$teacher);
					}
				}

				$_GET['message'] = "获取列表成功";
				return $family_list;
				break;
			default:
				return SendMessage::sendErrorMsg("获取列表失败");
				break;
		}
	}

	public function actionTest($admin_id,$user_role,$studio_id,$name='') {
		
		if(!$mail = MailList::findOne(['role'=>$this->user_role,'user_id'=>$this->user_id])) {

			$mail =  MailList::Add($this->user_id,$this->user_role,$this->studio_id);
		}


		MailList::UpdateOld($this->user_id,$this->user_role);

		switch ($user_role) {
			case 'teacher':
				$teacher_list = [];

				$admin = Admin::findOne($admin_id);

				//散户学生
				$retail_student  = ChatUser::getStudent($studio_id);
				
				$retail_users = $this->GetStudent($retail_student,$name);
				//家长
				$family   = ChatFamily::getAll($studio_id,'',$name);

	
				if($admin->is_chat) {
					
					if($studio_id == 183){

						if($name){
							//散户老师
							$retail_teacher = ChatAdmin::getSanHu($studio_id);

							$item_name =  $admin->auths->item_name;

							$pid = substr($item_name,-3);

							$retail_teacher_list = $this->GetRetailTeachers($retail_teacher,$name);
							
							if(in_array($pid,Yii::$app->params['Shenfen'])) {
								
								$teacher_list[] = array('name'=>'散户老师','group_users'=>$retail_teacher_list);
							}
							
							if($retail_users){
								$teacher_list[] = array('name'=>'新增学生','group_users'=>$retail_users);
							}

							if($family){
								$teacher_list[] = array('name'=>'新增家长','group_users'=>$family);
							}
						}
					}else{
						
						if($retail_users){
							$teacher_list[] = array('name'=>'新增学生','group_users'=>$retail_users);
						}

						if($family){
							$teacher_list[] = array('name'=>'新增家长','group_users'=>$family);
						}	
					}
				}
				
				$teachers  =  $this->GetTeachers(array_column(Admin::getVisuaForChat($admin_id), 'admin_id'),$name);
				if($teachers){
					$teacher_list[] = array('name'=>'老师','group_users'=>$teachers);
				}

				$campuses =  Format::explodeValue(Admin::findOne($admin_id)->campus_id);

				//班级
				$classes  =  ChatClasses::getClsses($admin_id,$campuses,$name);
				

				//获取全部班级
				$teacher_calss = ChatClasses::getTeacherClsses($admin_id,$campuses);

				if($name) {
					ChatClasses::$user_name = $name;	
					ChatClassTeacher::$user_name = $name;
				}

				//班级老师

				$class_teachers = ChatClassTeacher::findAll(['id'=>$teacher_calss]);

				foreach ($class_teachers as $key => $value) {
					$teacher_list[] = $value;
				}

				//班级学生
				$class_students =  ChatClasses::findAll(['id'=>$classes]);

				foreach ($class_students as $key => $value) {
					$teacher_list[] = $value;
				}


				$_GET['message'] = "获取列表成功";

				return $teacher_list;

				break;
			case 'student':
				$student_list = [];
				//获取学生班级
				$class_id  = ChatUser::findOne($admin_id)->class_id;

				//获取所有老师
				$teachers = ChatAdmin::getTeacherByStudio($studio_id);
				
				$teacher = $this->GetTeachers($teachers,$name);

				if($teacher){
					$student_list[] = array('name'=>'老师','group_users'=>$teacher);
				}
				//家长
				$family    = ChatFamily::getAll($studio_id,$admin_id,$name);

				if($family) {
					$student_list[] = array('name'=>'家长','group_users'=>$family);
				}
				
				$_GET['message'] = "获取列表成功";
				return $student_list;

				break;
			case 'family':

				$family_list = [];
				//学生
				$relation_id = ChatFamily::findOne($admin_id)->relation_id;

				if($relation_id) {
					$student = $this->GetStudent($relation_id,$name);
					if($student){
						$family_list[] = array('name'=>'我的孩子','group_users'=>$student);
					}

					$class_id = SimpleUser::findOne($relation_id)->class_id;
					if($class_id){
						$teachers = ChatClasses::findOne($class_id)->supervisor;

						$teacher = $this->GetTeachers($teachers,$name);

						if($teacher){
							$family_list[] = array('name'=>'老师','group_users'=>$teacher);
						}
					}
				}else{
					$teachers = ChatAdmin::getTeacherByStudio($studio_id);
					$teacher  = $this->GetTeachers($teachers,$name);
					if($teacher){
						$family_list[] = array('name'=>'老师','group_users'=>$teacher);
					}
				}

				$_GET['message'] = "获取列表成功";
				return $family_list;
				break;
			default:
				return SendMessage::sendErrorMsg("获取列表失败");
				break;
		}
	}
	//获取详细信息
	public function actionGetInfo($admin_id,$user_role) {
		$_GET['message'] = '获取信息成功';
		switch ($user_role) {
			case 'teacher':
				return ChatInfoAdmin::findOne($admin_id);
				break;
			case 'student': 
				return ChatInfoUser::findOne($admin_id);
				break;
			case 'family': 
				return ChatInfoFamily::findOne($admin_id);
				break;
		}
	}
	
	//批量更新学生studio_id
	public function actionSetStudio() {
		return ActivationCode::getXiaoZhang();

		$users = ChatUser::find()
                            ->select(['admin_id'=>'id','name','usersig','studio_id','image','is_image'])
						    #->where(['studio_id'=>NULL])
						    ->where(['NOT', ['campus_id' => null]])
						    ->createCommand()->getRawSql();
						    #->all();
		return $users;
	}

	public function GetStudent($ids , $name) {
		$users = ChatUser::find()
                            ->select(['admin_id'=>'id','name','usersig','studio_id','image','is_image'])
						    ->where(['id'=>$ids])
						    ->andWhere(['NOT',['usersig'=>NULL]])
						    ->andWhere(['NOT',['name'=>NULL]])
						    ->andFilterWhere(['like', 'name', $name])
						    ->orderBy('id DESC')
						    ->all();
		return $users;
	}

	//散户老师

	public function GetRetailTeachers($ids , $name) {
		$teachers = ChatAdmin::find()
						    ->where(['id'=>$ids])
						    ->andWhere(['NOT',['usersig'=>NULL]])
						    ->andWhere(['NOT',['name'=>NULL]])
						    ->andFilterWhere(['like', 'name', $name])
						    ->all();
		return $teachers;
	}


	public function GetTeachers($ids , $name) {
		$teachers = ChatAdmin::find()
							->joinWith('codes')
						    ->where(['admin.id'=>$ids])
						    ->andWhere(['NOT',['admin.usersig'=>NULL]])
						    ->andWhere(['NOT',['admin.name'=>NULL]])
						    ->andFilterWhere(['like', 'admin.name', $name])
						    ->all();
		return $teachers;
	}

	public function GetFamilys($ids ,$name) {
		$teachers = ChatFamily::find()
						    ->where(['id'=>$ids])
						    ->andWhere(['NOT',['usersig'=>NULL]])
						    ->andWhere(['NOT',['name'=>NULL]])
						    ->andFilterWhere(['like', 'name', $name])
						    ->orderBy('id DESC')
						    ->all();
		return $teachers;
	}

}