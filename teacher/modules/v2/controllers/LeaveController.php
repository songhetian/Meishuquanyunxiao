<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Format;
use common\models\Leave;
use common\models\LeaveAudit;
use common\models\LeaveRule;
use teacher\modules\v2\models\Common;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Family;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\CodeAdmin;
use components\Upload;
use components\Oss;

class LeaveController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Leave';

	//获取创建时所需的数据
	public function actionGetCreateData()
	{
		$modelClass = $this->modelClass;

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		//请假类型
		$types = Leave::getValues('type');
		foreach ($types as $k => $v) {
        	$res['leaveType'][] = [
        		'label' => $v,
        		'value' => (string)$k
        	];
		}

		//家长列表
		if($user_role == 'student'){
			$account = User::findOne($account_id);
			$familys = Family::findAll(['relation_id' => $account_id, 'status' => Family::STATUS_ACTIVE]);
			if($familys){
				foreach ($familys as $k => $v) {
		        	$family[] = [
		        		'id' => $v->id,
						'user_role' => 'family',
						'name' => $v->name,
						'image' => $modelClass::getImage('family', NULL, $v->image)
		        	];
				}
			}
			$res['appover'][] = [
				'name' => '家长',
				'people' => ($family) ? $family : [] 
			];

			$rule = LeaveRule::findOne(['campus_id' => $account->campus_id, 'status' => LeaveRule::STATUS_ACTIVE]);
			if($rule){
				$res['rule'] = [
					'student' => $rule->student,
				];
			}
		}else{
			$account = Admin::findOne($account_id);
		}

		//老师列表
		$teachers = Admin::find()->where([
			'or like',
			Format::concatField('campus_id'),
			Format::concatString($account->campus_id)
		])
		->andFilterWhere(['NOT', ['name' => 'NULL']]) 
		->andFilterWhere(['status' => Admin::STATUS_ACTIVE]) 
		->all();

		if($teachers){
			foreach ($teachers as $k => $v) {
				$role = Yii::$app->authManager->getRolesByUser($v->id);
	        	$teacher[] = [
	        		'id' => $v->id,
					'user_role' => 'teacher',
		        	'name' => $v->name . '(' . $role[key($role)]->description . ')',
		        	'image' => $modelClass::getImage('teacher', $account->campus_id, $v->image)
	        	];
			}
		}

		$res['appover'][] = [
			'name' => '老师',
			'people' => ($teacher) ? $teacher : []
		];

		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
		
		return $res;
	}

	//创建请假信息
	public function actionCreate()
    {
      	$model = new Leave(['scenario' => 'create']);
		if($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Leave Success');
			return [];
		}
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }

    //获取首页数据
    public function actionGetList()
	{
		$modelClass = $this->modelClass;

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		switch ($user_role) {
			case 'family':
				$students = $modelClass::createData(Family::findOne($account_id)->relation_id);
				break;
			case 'teacher':
				$students = $modelClass::createData($modelClass::getIds('\common\models\User', $account_id));
				$teachers = $modelClass::createData($modelClass::getIds('\backend\models\Admin', $account_id));
				break;
		}

		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

		return [
			'dataInfo' => Yii::$app->params['leave'][$user_role],
			'studentList' => ($students) ? $students : [],
			'teacherList' =>  ($teachers) ? $teachers : []
		];
	}
	
	public function actionGetStudentMore()
	{
		$modelClass = $this->modelClass;

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		switch ($user_role) {
			case 'family':
				$students = $modelClass::createData(Family::findOne($account_id)->relation_id, $page, $limit);
				break;
			case 'teacher':
				$students = $modelClass::createData($modelClass::getIds('\common\models\User', $account_id), $page, $limit);
				break;
		}
		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
		return ($students) ? $students : [];
	}

	public function actionGetTeacherMore()
	{
		$modelClass = $this->modelClass;

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		$teachers = $modelClass::createData($modelClass::getIds('\backend\models\Admin', $account_id), $page, $limit);

		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
		return ($teachers) ? $teachers : [];
	}

	//我审批的
	public function actionGetAudit()
	{
		$modelClass = $this->modelClass;

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		$ids = LeaveAudit::find()
		->andFilterWhere(['audit_id' => $account_id])
		->andFilterWhere(['or', [
			'processing_state' => [
				LeaveAudit::PROCESSING_STATE_NOT_YET,
				LeaveAudit::PROCESSING_STATE_ED,
				LeaveAudit::PROCESSING_STATE_REFUSE
			]
		]])
		->andFilterWhere(['status' => LeaveAudit::STATUS_ACTIVE])
		->offset($page * $limit)
		->limit($limit)
		->indexBy('leave_id')
		->orderBy('processing_state ASC')
        ->all();

		if($ids){
			$ids = array_keys($ids);
			$model = $modelClass::find()
	        ->andFilterWhere(['in', 'id', $ids])
	        ->andFilterWhere(['status' => $modelClass::STATUS_ACTIVE])
	        ->orderBy(['FIELD (`id`,' . Format::implodeValue($ids) . ')' => ''])
	        ->all();
	        $res = $modelClass::DataInfo($model, 20);
		}

		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');

		return ($res) ? $res : [];
	}

	//我发起的
	public function actionGetInitiate()
	{
		$modelClass = $this->modelClass;

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		$model = $modelClass::find()
        ->where(['account_id' => $account_id, 'status' => $modelClass::STATUS_ACTIVE])
        ->offset($page * $limit)
		->limit($limit)
        ->all();

        $res = $modelClass::DataInfo($model, 20);

        $_GET['message'] = Yii::t('teacher','Sucessfully Get List');

        return ($res) ? $res : [];
	}

	//请假详情信息
	public function actionGetAuditInfo()
	{
		$modelClass = $this->modelClass;

		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$source = Yii::$app->request->post('source');
		if(!$source){
			return SendMessage::sendErrorMsg('source不能为空');
		}

		$model = $modelClass::findOne($id);
		
		//图片处理
		$tname = $modelClass::getTname($model->user_role);
		if($model->image){
            $exps = Format::explodeValue($model->image);
            foreach ($exps as $v) {
                $images[] = [
                    'url' => $modelClass::getImage($model->user_role, $model->$tname->campus_id, $v, 'leave')
                ];
            }
        }

        $steps = $modelClass::getProcessingState($model, 20);
        array_unshift($steps, [
        	'status' => 'finish',
            'nameInfo' => $model->$tname->name . ' 发起申请',
            'timeInfo' => date('Y-m-d', $model->created_at)
        ]);
        $icons = [];
    	if($source == 10){
    		$audit = LeaveAudit::findOne([
	        	'leave_id' => $id,
	        	'user_role' => $user_role,
	        	'audit_id' => $account_id,
	        	'processing_state' => LeaveAudit::PROCESSING_STATE_NOT_YET
	        ]);
	        if($audit){
	        	$icons = [
	        		[
	        			'type' => LeaveAudit::PROCESSING_STATE_ED,
	        			'name' => '同意',
	        		],
	        		[
	        			'type' => LeaveAudit::PROCESSING_STATE_REFUSE,
	        			'name' => '拒绝',
	        		],
	        	];
        	}
        }else{
        	if($model->user_role == $user_role && $model->account_id == $account_id){
        		$icons = [
	        		[
	        			'type' => LeaveAudit::PROCESSING_STATE_CLOSE,
	        			'name' => '撤销',
	        		]
	        	];
        	}
        }
        
        //额外需求 增加校长可删除功能
        if($user_role == 'teacher'){
        	$item_name =  CodeAdmin::findOne($account_id)->auths->item_name;
        	$pid = substr($item_name, -3);
			if($pid == '001') {
				$icons += [
	        		[
	        			'type' => LeaveAudit::PROCESSING_STATE_DELETE,
	        			'name' => '删除',
	        		]
	        	];
			}
        }
        
        $_GET['message'] = Yii::t('teacher','Sucessfully Get List');
        $models[] = $model;
        return [
        	'dataInfo' => $modelClass::DataInfo($models, 30),
        	'dataImage' => ($images) ? $images : [],
        	'dataSteps' => ($steps) ? $steps : [],
        	'dataIcons' => ($icons) ? $icons : []
        ];
	}

	//更改状态
	public function actionHandle()
	{
		$modelClass = $this->modelClass;
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$type = Yii::$app->request->post('type');
		if(!$type){
			return SendMessage::sendErrorMsg('type不能为空');
		}

		$user_role = Yii::$app->request->post('user_role');
		if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		//撤销
		if($type == LeaveAudit::PROCESSING_STATE_CLOSE || $type == LeaveAudit::PROCESSING_STATE_DELETE){
			$leave = $modelClass::findOne($id);
			$leave->status = $modelClass::STATUS_DELETED;
			if($leave->save()){
				if($type == LeaveAudit::PROCESSING_STATE_CLOSE){
					$_GET['message'] = Yii::t('teacher','已撤销!');
				}else{
					$_GET['message'] = Yii::t('teacher','已删除!');
				}
				return [];
			}
		}

		$model = LeaveAudit::findOne([
        	'leave_id' => $id,
        	'user_role' => $user_role,
        	'audit_id' => $account_id,
        	'processing_state' => LeaveAudit::PROCESSING_STATE_NOT_YET
        ]);
        if(!$model){
        	$_GET['message'] = Yii::t('teacher','操作失败,当前审批人不是您!');
        	return [];
        }

		switch ($type) {
			case LeaveAudit::PROCESSING_STATE_ED:
				$model->processing_state = $type;
				$model->processing_at = time();
				if($model->save()){
					$_GET['message'] = Yii::t('teacher','已审批!');
					//轮询到下一个人审批
					$position = $model->position + 1;
					$next = LeaveAudit::findOne(['leave_id' => $model->leave_id, 'position' => $position]);
					if($next){
						$next->processing_state = LeaveAudit::PROCESSING_STATE_NOT_YET;
						$next->save();
					}
				}
				break;
			//拒绝
			case LeaveAudit::PROCESSING_STATE_REFUSE:
				$model->processing_state = $type;
				if($model->save()){
					$_GET['message'] = Yii::t('teacher','已拒绝!');
				}
				break;
		}
        return [];
	}

    //上传请假图片
    public function actionUpload()
    {
    	$user_role = Yii::$app->request->post('user_role');
    	if(!$user_role){
			return SendMessage::sendErrorMsg('user_role不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}
		
		$account = Common::getAccount($user_role, $account_id);
		if(!$account){
			return SendMessage::sendErrorMsg('请假人不存在');
		}
		$studio = Campus::findOne($account->campus_id)->studio_id;
		
		if(!$_FILES){
			return SendMessage::sendErrorMsg('上传文件为空');
		}
		$_GET['message'] = Yii::t('teacher','Upload Success');
		$image = Upload::pic_upload($_FILES, $studio, 'leave', 'image')['image'];
		$size = Yii::$app->params['oss']['Size']['320x320'];
        return [
        	'url' => Oss::getUrl($studio, 'leave', 'image', $image).$size,
        	'image_name' => $image
        ];
    }
}