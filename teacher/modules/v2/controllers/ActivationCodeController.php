<?php 
	namespace teacher\modules\v2\controllers;

	use yii;
	use components\Code;
	use common\models\Campus;
	use yii\base\ErrorException;
	use common\models\Format;
	use teacher\modules\v2\models\User;
	use teacher\modules\v1\models\Errors;
	use teacher\modules\v2\models\Admin;
	use teacher\modules\v2\models\Rbac;
	use teacher\modules\v2\models\Studio;
	use teacher\modules\v1\models\SendMessage;
	use teacher\modules\v2\models\CodeAdmin;
	use teacher\modules\v2\models\CodeCategory;
	use teacher\modules\v2\models\ActivationCode;
	use teacher\modules\v2\models\NewActivationCode;

	class ActivationCodeController extends MainController {

		public $modelClass = 'teacher\modules\v2\models\ActivationCode';

		/*
		 *[激活码创建] POST
		 * @param [role 角色 numbner 生成数量 ]
		 *
		 *
		*/

		public function actionCreate() {
			error_reporting(0); 
			$modelClass = $this->modelClass;

			$type  = Yii::$app->request->post('type');

			$activetime = Yii::$app->request->post('activetime');
			if(Yii::$app->request->post('type') == 2) {
				if(!Yii::$app->request->post('class_id')) {
					  return SendMessage::sendErrorMsg("该校区下没有班级,请创建");
				}
			}

			$admin_id  = Yii::$app->request->post('admin_id');

			if(CodeAdmin::findOne($admin_id)->is_create_number != 10) {
				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}
		
			$item_name =  CodeAdmin::findOne($admin_id)->auths->item_name;

			$pid = substr($item_name,-3);

			//判断数量
			$studio = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id;

			if(!in_array($studio,Yii::$app->params['Studio'])) {


				if(!in_array($pid,Yii::$app->params['OhterShenfen'])) {
					return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
				}

				//是否超过激活数量
				if(!NewActivationCode::getCodeNum($type,$activetime,$studio,Yii::$app->request->post('number'))) {

					return SendMessage::sendErrorMsg(Yii::t('teacher','Code Exceed'));
				}
			}else{
				if(!in_array($pid,Yii::$app->params['Shenfen'])) {
					return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
				}
			}

			$code   =  new Code(Yii::$app->request->post('number'));

			$list   =  $code->create();

			$model = new ActivationCode();

			$model->setScenario('create');

			if(Yii::$app->request->post('type') == 1){
				$create_model  = new Admin();
				$create_model->setScenario('create');
				$create_model->password_hash = '123456';
				$create_model->studio_id = $studio;
				$create_model->load(Yii::$app->getRequest()->getBodyParams(), '');
				if(Yii::$app->request->post('class_id') == 0){
					$create_model->class_id = NULL;
				}
			}else if(Yii::$app->request->post('type') == 2){
				$create_model  = new User();
				$create_model->setScenario('create');
				$create_model->load(Yii::$app->getRequest()->getBodyParams(), '');
				$create_model->studio_id  = $studio;
				$create_model->is_review  = User::REVIEW_ED;
			}

 			$connect = Yii::$app->db->beginTransaction();

 			try{
				foreach ($list as $value) {
					$_create_model = clone $create_model;
					if(!$_create_model->save()) {	
						throw new ErrorException(Errors::getInfo($_create_model->getErrors()));	
					}
	
					$_model = clone $model;
					$_model->relation_id = $_create_model->id;
					$_model->campus_id   = $_create_model->campus_id;
					$_model->class_id    = $_create_model->class_id;
					$_model->code = $value;
					$_model->studio_id = $studio;
					$_model->type = Yii::$app->request->post('type');
					$_model->activetime = $activetime;
					$_model->is_first   = 0;
					if(!$_model->save()) {
						throw new ErrorException(Errors::getInfo($_model->getErrors()));
					}
				}
				$connect->commit();

				return SendMessage::sendSuccessMsg(Yii::t('teacher','Code Success'));
			} catch (ErrorException $e) {
			    $connect->rollBack();
			    return SendMessage::sendErrorMsg($e->getMessage());
			}
		}
		/*
		 *[激活码创建] POST
		 * @param [role 角色 numbner 生成数量 ]
		 *
		 *
		*/

		public function actionCreateNoUser() {

			error_reporting(0); 

			$modelClass = $this->modelClass;

			$student_id = Yii::$app->request->post('student_id');
			//判断数量
			$studio = User::findOne($student_id)->studio_id;

			$total_number = Studio::getNumber($studio,Yii::$app->request->post('activetime'));

			$number =  $modelClass::getNumber($studio,Yii::$app->request->post('activetime'),2);

			$admin_id  = Yii::$app->request->post('admin_id');

			$item_name =  CodeAdmin::findOne($admin_id)->auths->item_name;

			$pid = substr($item_name,-3);

			if(!in_array($pid,Yii::$app->params['Shenfen'])) {

				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}


			if(!in_array($studio,Yii::$app->params['Studio'])) {
				//是否超过激活数量
				if($number + Yii::$app->request->post('number') > $total_number) {

					return SendMessage::sendErrorMsg(Yii::t('teacher','Code Exceed'));
				}
			}

			if(ActivationCode::findOne(['relation_id'=>$student_id,'type'=>2,'status'=>10])){
				return SendMessage::sendErrorMsg("该用户已经绑定");
			}
			$code   =  new Code(1);

			$user   = User::findOne($student_id);

			$code_number   =  $code->CreateOne();

			$model = new ActivationCode();

			$model->setScenario('create');

 			$connect = Yii::$app->db->beginTransaction();

 			try{
				$model->campus_id   = Yii::$app->request->post('campus_id');
				$model->class_id    = Yii::$app->request->post('class_id');
				$model->code = $code_number;
				$model->studio_id = $studio;
				$model->relation_id = $student_id;
				$model->type = 2;
				$model->activetime = Yii::$app->request->post('activetime');
				if(!$model->save()) {
					throw new ErrorException(Errors::getInfo($model->getErrors()));
				}
				$user->campus_id   = Yii::$app->request->post('campus_id');
				$user->class_id    = Yii::$app->request->post('class_id');
				if(!$user->save()) {
					throw new ErrorException(Errors::getInfo($user->getErrors()));
				}
				$connect->commit();
				return SendMessage::sendSuccessMsg(Yii::t('teacher','Code Success'));
			} catch (ErrorException $e) {
			    $connect->rollBack();
			    return SendMessage::sendErrorMsg($e->getMessage());
			}
		}


		//获取角色

		public function actionGetRoels() {

			error_reporting(0); 

			$admin_id = Yii::$app->request->post('admin_id');

			$type = Yii::$app->request->post('type');

			$studio_id = Admin::findOne($admin_id)->studio_id;

			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

			return Rbac::getRolesApi($studio_id,$type);
		}

		//获取角色

		public function actionGetRoelsNoActive() {
			error_reporting(0); 
			$admin_id = Yii::$app->request->post('admin_id');

			$type = Yii::$app->request->post('type');

			$studio_id = Admin::findOne($admin_id)->studio_id;

			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

			return Rbac::getRolesApi1($studio_id,$type);
		}

		//获取课件校区
		public function actionGetCampus() {

			 $admin_id = Yii::$app->request->post('admin_id');
			 $list = Admin::GetCampus($admin_id);

			 $_GET['message'] = Yii::t('teacher','Sucessfully Get List');

			 return $list;
		}

		//获取班级列表
		public function actionGetClasses() {

			$admin_id   = Yii::$app->request->post('admin_id');
			$campus_id  = Yii::$app->request->post('campus_id');
			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
			return  $list = \teacher\modules\v2\models\Classes::getClsses($admin_id,$campus_id);
		}

		//获取科目
		public function actionGetCategory() {

			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

			if($studio_id = Yii::$app->request->post('studio_id')) {
				$studio_type = Studio::findOne($studio_id)->type;
			}else{
				$studio_type = 1;
			}
			
			$list = CodeCategory::find()->where(['pid'=>0,'type'=>10,'studio_type'=>$studio_type])->all();

			return $list;
		}


		//生成状态
		public function actionGetActive () {
			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
			return array(
				array(
					'title' => '全部激活状态',
					'is_active' => "002"
				),
				array(
					'title' => '未激活',
					'is_active' => 20
				),
				array(
					'title' => '已激活',
					'is_active' => 10
				),
				array(
					'title' => '已过期',
					'is_active' => 30
				),
			);
		}
		/**
		 *[激活码统计]
		 *
		 *
		*/
		public function actionList() {

			$modelClass = $this->modelClass;

			$admin_id = Yii::$app->request->post('admin_id');

			$studio_id = Admin::findOne(Yii::$app->request->post('admin_id'))->studio_id;

			$item_name =  CodeAdmin::findOne($admin_id)->auths->item_name;

			$pid = substr($item_name,-3);

			// if(in_array($studio_id, Yii::$app->params['Studio'])) {
			// 	if(!in_array($pid,Yii::$app->params['Shenfen'])) {

			// 		return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			// 	}
			// }
	        if(!in_array($studio_id,Yii::$app->params['Studio'])) {

	            if(!in_array($pid,Yii::$app->params['OhterShenfen'])) {
	                return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
	            }
	        }else{
	            if(!in_array($pid,Yii::$app->params['Shenfen'])) {
	                return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
	            }
	        }
			$student = array(

					 	array(
						 	'id' => 4,
						 	'navTitle'     => "学生使用一年数量",
						 	'TotalName'    => "使用一年总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,1),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,1),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,1,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,1,$modelClass::USE_DELETED),
						),
					 	array(
						 	'id' => 5,
						 	'navTitle'     => "学生使用二年数量",
						 	'TotalName'    => "使用二年总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,2),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,2),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,2,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,2,$modelClass::USE_DELETED),
						),
					 	array(
						 	'id' => 6,
						 	'navTitle'     => "学生使用三年数量",
						 	'TotalName'    => "使用三年总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,3),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,3),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,3,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,3,$modelClass::USE_DELETED),
						),
                        array(
                             'id' => 11,
                             'navTitle'     => "学生使用三月数量",
                             'TotalName'    => "2021试用总数",
                             'TotalNumber'  => Studio::getNumber($studio_id,0.25),
                             'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.25),
                             'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.25,$modelClass::USE_ACTIVE),
                             'haveNumber'   => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.25,$modelClass::USE_DELETED),
                        )
					 );

			//1个月数量
			$freeList   =   array(
								array(
								 	'id' => 7,
								 	'navTitle'     => "学生使用有一月数量",
								 	'TotalName'    => "学生数量",
								 	'TotalNumber'  => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09),
								 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09),
								 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09,$modelClass::USE_ACTIVE),
								 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09,$modelClass::USE_DELETED),
							    ),
								array(
								 	'id' => 10,
								 	'navTitle'     => "老师使用一月数量",
								 	'TotalName'    => "老师数量",
								 	'TotalNumber'  => $modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0.09),
								 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0.09),
								 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0.09,$modelClass::USE_ACTIVE),
								 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0.09,$modelClass::USE_DELETED),
							    )
						);

			if(in_array($studio_id, Yii::$app->params['Studio'])) {

				$new = array(
					 	array(
						 	'id' => 7,
						 	'navTitle'     => "学生使用一月数量",
						 	'TotalName'    => "使用一月总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,0.09),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.09,$modelClass::USE_DELETED),
						),
					 	array(
						 	'id' => 8,
						 	'navTitle'     => "学生使用三月数量",
						 	'TotalName'    => "使用三月总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,0.25),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.25),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.25,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.25,$modelClass::USE_DELETED),
						),
					 	array(
						 	'id' => 9,
						 	'navTitle'     => "学生使用六月数量",
						 	'TotalName'    => "使用六月总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,0.5),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.5),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.5,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.5,$modelClass::USE_DELETED),
						),
					 	array(
						 	'id' => 10,
						 	'navTitle'     => "学生使用一周数量",
						 	'TotalName'    => "使用一周概况",
						 	#'TotalNumber'  => Studio::getNumber($studio_id,0.09),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.019),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.019,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_USER,0.019,$modelClass::USE_DELETED),
						),
				);

				$student = array_merge($student,$new);

			}

			$list = array(

				array(
					 'id'   => 1,
					 'key'  => "教师(个)",
					 'data' => array(
					 	array(
						 	'id' => 2,
						 	'navTitle'     => "教师管理",
						 	'TotalName'    => "总数",
						 	'TotalNumber'  => Studio::getNumber($studio_id,0),
						 	'OkNumber'     => $modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0),
						 	'activeNumber' => $modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0,$modelClass::USE_ACTIVE),
						 	'haveNumber'   =>$modelClass::getCount($studio_id,$modelClass::TYPE_TEACHER,0,$modelClass::USE_DELETED),
						),

					 ),
				),
				array(
					 'id'   => 3,
					 'key'  => "学生(个)",
					 'data' => $student,
				),
			);

			if(!in_array($studio_id, Yii::$app->params['Studio'])) {
				$free = array(
						 'id'   => 20,
						 'key'  => "一月数量[".Studio::getNumber($studio_id,0.09)."个](老师+学生)",
						 'data' => $freeList,
				);
				array_push($list,$free);
			}

			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

			return $list;
		}

		//激活码列表详情
		/**
		* $admin_id,$role='',$is_active = '',$page=0,$limit=10
		*
		*
		*/
		public function actionListInfo() {

			$modelClass = $this->modelClass;

			$page   = Yii::$app->request->post('page');

			$limit  = Yii::$app->request->post('limit');

			$admin_id = Yii::$app->request->post('admin_id');

			$test    =  Yii::$app->request->post('text');

			$campus_id = Admin::findOne($admin_id)->campus_id;

			$offset = $page*$limit;

			$Admin = CodeAdmin::findOne($admin_id);

			$item_name =  $Admin->auths->item_name;

			$studio_id  = $Admin->studio_id;

			$pid = substr($item_name,-3);


			if(Yii::$app->request->post('role') == "001") {

				$role = NULL;
			}else{

				$role = Yii::$app->request->post('role');
			}

			if(Yii::$app->request->post('is_active') == "002") {
				$is_active = NULL;
			}else{
				$is_active = Yii::$app->request->post('is_active');
			}
			$admins =  CodeAdmin::getTeachers($campus_id,$studio_id,$admin_id,$offset,$limit);

			$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

			$time = date("Y-m-d",time());

			if($is_active == 20 || $is_active == 10 || $is_active == NULL){
				$list1 =  CodeAdmin::find()
				                  ->joinWith('codes')
				                  ->joinWith('auths')
								  ->where(['admin.id'=>$admins,'admin.status'=>CodeAdmin::STATUS_ACTIVE,'codes.type'=>1])
								  ->andWhere(['>=','codes.due_time',$time])
								  ->andFilterWhere(['codes.is_active'=>$is_active])
								  ->andFilterWhere(['auths.item_name'=>$role])
								  ->andFilterWhere([
				                        'or',
				                        ['like', 'admin.name', $test],
				                        ['like', 'admin.phone_number', $test],
				                        ['like', 'codes.code',strtoupper($test)]
				                    ])
								  ->orderBy(["admin.created_at" => SORT_DESC])
								  ->all();
				
				$list2  = CodeAdmin::find()
			                    ->where([
			                        'studio_id' => $studio_id,
			                        'status' => 10,
			                        'campus_id' => NULL
			                    ])
			                    ->orWhere([
			                    	'campus_id'=>'',
			                    	'studio_id' => $studio_id,
			                        'status' => 10,
			                    ])
			                    ->andFilterWhere(['or',['like', 'name', $test],
			                                           ['like', 'phone_number', $test]
			                                    ])
							    ->offset($offset)
							    ->limit($limit)
		                        ->all(); 

		        return array_merge($list1,$list2);

			}elseif($is_active == 30){
				return CodeAdmin::find()
				                  ->joinWith('codes')
				                  ->joinWith('auths')
								  ->where(['admin.id'=>$admins,'admin.status'=>CodeAdmin::STATUS_ACTIVE,'codes.type'=>1])
								  ->andWhere(['<','codes.due_time',$time])
								  ->andFilterWhere(['auths.item_name'=>$role])
								  ->andFilterWhere([
				                        'or',
				                        ['like', 'admin.name', $test],
				                        ['like', 'admin.phone_number', $test],
				                        ['like', 'codes.code',strtoupper($test)]
				                    ])
								  ->orderBy(["admin.created_at" => SORT_DESC])
								  ->all();
			}elseif($is_active == 40) {
			        $retail = CodeAdmin::find()
			                    ->where([
			                        'studio_id' => $studio_id,
			                        'status' => 10,
			                        'campus_id' => NULL
			                    ])
			                    ->orWhere([
			                    	'campus_id'=>'',
			                    	'studio_id' => $studio_id,
			                        'status' => 10,
			                    ])
			                    ->andFilterWhere(['or',['like', 'name', $test],
			                                           ['like', 'phone_number', $test]
			                                    ])
							    ->offset($offset)
							    ->limit($limit)
			                    ->indexBy('id')
		                        ->asArray()
		                        ->all();  
			       return  $sanhu = CodeAdmin::find()
							        ->where(['id' => array_keys($retail)])
							        ->all();

			}
		}

		//重新激活
		public function actionReset() {
			$modelClass = $this->modelClass;
			$connect = Yii::$app->db->beginTransaction();

			$code_id = Yii::$app->request->post('code_id');

			$model = \common\models\ActivationCode::findOne(['code'=>$code_id]);
	
			$model->is_active = $modelClass::USE_DELETED;

			$code          =  new Code();

        	$token_value   =  $code->CreateToken(uniqid());

       		if($model->type == ActivationCode::TYPE_TEACHER) {
       			$user_model = Admin::findOne($model->relation_id);
       		}elseif($model->type == ActivationCode::TYPE_USER) {
       			$user_model = User::findOne($model->relation_id);
       		}

       		$user_model->token_value = $token_value;

       		$user_model->usersig     = NULL;

 			try{
				if(!$model->save()) {
					throw new ErrorException(Errors::getInfo($model->getErrors()));
				}

				if(!$user_model->save()) {
					throw new ErrorException(Errors::getInfo($user_model->getErrors()));
				}
				$connect->commit();
				return SendMessage::sendSuccessMsg(Yii::t('teacher','Rest Success'));
			} catch (ErrorException $e) {
			    $connect->rollBack();
			    return SendMessage::sendErrorMsg(Yii::t('teacher','Rest Fail'));
			}




			if ($model->save()) {
				return SendMessage::sendSuccessMsg(Yii::t('teacher','Rest Success'));
			}else{
				return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Rest Fail'));

			}
		}

		//删除
		public function actionCodeDelete() {
			error_reporting(0); 
			$modelClass = $this->modelClass;

			$code_id = Yii::$app->request->post('code_id');

			$admin_id  = Yii::$app->request->post('admin_id');

			$item_name =  CodeAdmin::findOne($admin_id)->auths->item_name;

			$pid = substr($item_name,-3);

			if($pid != "001") {
				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}

			$model = $modelClass::findOne(['code'=>$code_id]);


			$model->is_active = $modelClass::USE_ACTIVE;

			#$model->status    = $modelClass::STATUS_DELETED;
	
			$teacher =  CodeAdmin::findOne($model->relation_id);

			#$teacher->status = $modelClass::STATUS_DELETED;

			$teacher->name  = NULL;

			$teacher->image = NULL;

			$teacher->token_value = NULL;

			$teacher->phone_number = NULL;
			
			$role = Yii::$app->authManager->getRolesByUser($teacher->id);

            $teacher->role =  current($role)->name;
            
 			$connect = Yii::$app->db->beginTransaction();

 			try{
 				if(!$model->save()) {
 					throw new ErrorException(Errors::getInfo($model->getErrors()));	
 				}
 				if(!$teacher->save()) {
 					throw new ErrorException(Errors::getInfo($teacher->getErrors()));	
 				}
				$connect->commit();
				return SendMessage::sendSuccessMsg(Yii::t('teacher','Teacher Del Success'));
			} catch (ErrorException $e) {
			    $connect->rollBack();
			    return SendMessage::sendErrorMsg($e->getMessage());
			}
		}

		//生成
		public function actionCreateOne() {

			$modelClass = $this->modelClass;

			$id       = Yii::$app->request->post('id');

			$type     = Yii::$app->request->post('type');

			$admin_id = Yii::$app->request->post('admin_id');

			$activetime = Yii::$app->request->post('activetime');

			$activetime = $activetime ? $activetime : 1;

			$studio_id = Admin::findOne($admin_id)->studio_id;

			if(!in_array($studio_id,Yii::$app->params['Studio'])) {
				//是否超过激活数量
				if(!NewActivationCode::getCodeNum($type,$activetime,$studio_id)) {

					return SendMessage::sendErrorMsg(Yii::t('teacher','Code Exceed'));
				}
			}

			$code   =  new Code(1);

			if($type == $modelClass::TYPE_TEACHER){
				$user = Admin::findOne($id);
			}elseif($type == $modelClass::TYPE_USER) {
				$user = User::findOne($id);
			}

			// if($type == $modelClass::TYPE_TEACHER) {
			// 	$activetime = 0;
			// }

			// else if($type == $modelClass::TYPE_USER) {
			//     $activetime = $user->years;
			// }

			$list   =  $code->create();
			$model = new ActivationCode();

			$model->setScenario('create');

			$model->relation_id = $id;
			$model->code = current($list);
			$model->studio_id = $studio_id;
			$model->type = $type;
			$model->activetime = $activetime;

			$user->token_value = NULL;
			
			if($model->save() && $user->save()) {

				$_GET['message'] = Yii::t('teacher','Code Success');

				return array(
					'code'     => $model->code,
					'onClick'  => 2,
					'button'   => '复制'
				);
				
			}else{

				return SendMessage::sendVerifyErrorMsg($model,Yii::t('teacher','Code Fail'));
			}
		}

	//获取创建时长
	public function actionGetTime() {
		$studio_id = Yii::$app->request->post('studio_id');
		$type = Yii::$app->request->post('type');
		$_GET['message'] = "获取列表成功";

		$studio = Yii::$app->params['Studio'];

		$type = ($type) ? $type : 2;

		if(in_array($studio_id, $studio)) {
			return array(
				array('title'=>'7天','name'=>'0.019'),
				array('title'=>'1月','name'=>'0.09'),
				array('title'=>'3月','name'=>'0.25'),
				array('title'=>'6月','name'=>'0.50'),
				array('title'=>'1年','name'=>'1'),
				array('title'=>'2年','name'=>'2'),
				array('title'=>'3年','name'=>'3')
			);
		}else{

			if($type == 2) {
				return array(
					array('title'=>'1年','name'=>'1'),
					array('title'=>'2年','name'=>'2'),
					array('title'=>'3年','name'=>'3'),
					array('title'=>'1月','name'=>'0.09'),
					#array('title'=>'2021试用','name'=>'0.25'),
				);
			}else{
				return array(
					array('title'=>'1年','name'=>'1'),
					array('title'=>'1月','name'=>'0.09'),
					#array('title'=>'3月','name'=>'0.25')
				);				
			}
		}

	}

	}

 ?>