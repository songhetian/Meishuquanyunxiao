<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use components\Upload;
use components\Oss;
use common\models\Classes;
use teacher\modules\v2\models\Exam;
use common\models\Format;
use common\models\User;
use common\models\ExamReview;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Common;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ExamHomework;
use common\models\ExamHomeworkReview;

class ExamController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Exam';

	//获取创建时所需的数据
	public function actionGetCreateData()
	{
		$modelClass = $this->modelClass;

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		//类型
		$types = Exam::getValues('type');
		foreach ($types as $k => $v) {
        	$res['examType'][] = [
        		'label' => $v,
        		'value' => (string)$k
        	];
		}

		$categorys = $modelClass::getCreateCategorys();
		foreach ($categorys as $k => $v) {
        	$res['examCategory'][] = [
        		'label' => $v,
        		'value' => (string)$k
        	];
		}

		$classes = $modelClass::getCreateClasses($account_id);
		foreach ($classes as $k => $v) {
        	$res['examClasses'][] = [
        		'label' => $v,
        		'value' => (string)$k
        	];
		}

		//老师列表
		$teachers = $modelClass::getCreateExams($account_id);
		foreach ($teachers as $k => $v) {
			$res['examTeachers'][] = [
        		'label' => $v,
        		'value' => (string)$k
    		];
		}

		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
		
		return $res;
	}

	//创建考试信息
	public function actionCreate()
    {
      	$model = new Exam(['scenario' => 'create']);
		if($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Exam Success');
			return [];
		}
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }

	//更新考试信息
	public function actionUpdate()
    {
    	error_reporting(0);
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id');
		}
      	$model = $this->findModel($id);
      	$model->setScenario('create');
		if($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
			if($model->image){
				$model->image = explode('?',explode("%2F", $model->image[0])[3])[0];
			}
			if($model->save()){
				$_GET['message'] = "修改成功";
				return [];
			}
		}
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }
    
    //更新详情
    public function UpdateInfo($id,$account_id)
    {	
		$account = Common::getAccount('teacher', $account_id);

		$_GET = [
			'studio' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Sucessfully Get List')
		];

		$model = Exam::find()
		->select(['id', 'title', 'type', 'time', 'category_id', 'class_id', 'content', 'image', 'length', 'require'])
		->where(['id' => $id])
		->asArray()
		->one();

		//查询阅卷人
		$reviews = ExamReview::findAll(['exam_id' => $id, 'status' => ExamReview::STATUS_ACTIVE]);
        foreach ($reviews as $v) {
           $json[] = [
				'review_id' => $v->review_id
           ];
           $review_ids[] = $v->review_id;
        }
		#$model['review_id'] = json_encode($json, TRUE);

		$display  = $this->findModel($id);
		
		$info['id']   = $id;
		$info['Test'] = $model['title'];
		$info['TestContent'] = $model['content'];
		$info['TestRequire'] = $model['require'];
		$info['TestTime']    = $model['length'];
		$info['review_id']   = json_decode(json_encode($json, TRUE));
		$info['LeaveType']   = Exam::getReview($review_ids);
		$info['CLassSchool'] = Exam::getClassesName($display['class_id']);
		$info['class_id']    = $model['class_id'];
		$info['time']        = $model['time']*1000;
		$info['TestTime']    = date("Y-m-d H:i:s",$model['time']);
		$info['examTypeV']   = array($model['type']);
		$info['examCategoryValue'] = array($model['category_id']);
		$info['length']      = $model['length'];
		$info['imageUrlCame']      = [Oss::getUrl(Format::getStudio('id', $account->campus_id), 'exam', 'image',$model['image']).Yii::$app->params['oss']['Size']['fix_width']];

		return $info;
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

		$account = Common::getAccount($user_role, $account_id);

		$_GET = [
			'source' => 10,
			'user_role' => $user_role,
			'message' => Yii::t('teacher','Sucessfully Get List')
		];

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		$res['List'] = $modelClass::createData($user_role, $account, $page, $limit);

		return $res;
	}

	//获取考试详情页
	public function actionGetExamInfo()
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
		
		$account = Common::getAccount($user_role, $account_id);

		$_GET = [
			'studio' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Sucessfully Get List')
		];

		$model = $this->findModel($id);
		return [
			'info'  =>    $model,
			'icons' =>    $modelClass::getIcons($type, $user_role, $account_id, $model),
			'infoPath' => $this->UpdateInfo($id,$account_id)
		];
	}

	//获取阅卷列表
	public function actionGetReviewList()
	{
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$account = Common::getAccount('teacher', $account_id);

		$_GET = [
			'source' => 10,
			'studio_id' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Sucessfully Get List')
		];

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		$res['List'] = ExamHomework::find()
		->where([
			'exam_id' => $id,
			'status' => ExamHomework::STATUS_ACTIVE,
			//'review_state' => ExamHomework::REVIEW_STATE_NOT_YET
		])
		->offset($page * $limit)
        ->limit($limit)
        ->orderBy('created_at DESC')
        ->all();

        $res['Title'] = [
        	'已批阅' => ExamHomework::find()
        	->where([
	        	'exam_id' => $id,
	        	'status' => ExamHomework::STATUS_ACTIVE,
	        	'review_state' => ExamHomework::REVIEW_STATE_ED
        	])->count(),
        	'未批阅' =>  ExamHomework::find()
        	->where([
        		'exam_id' => $id,
        		'status' => ExamHomework::STATUS_ACTIVE,
        		'review_state' => ExamHomework::REVIEW_STATE_NOT_YET
        	])->count()
        ];

		return $res;
	}

	//作品详情
	public function actionGetHomeworkInfo()
	{
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}
		
		$account = Common::getAccount('teacher', $account_id);

		$_GET = [
			'studio' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Sucessfully Get List')
		];

		$model = ExamHomework::findOne($id);
		return [
			'info' => $model
		];
	}

	//作业打分
	public function actionHomeworkMark()
	{
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$score = Yii::$app->request->post('score');
		if(!$score){
			return SendMessage::sendErrorMsg('score不能为空');
		}

		$account = Common::getAccount('teacher', $account_id);
		$_GET = [
			'studio' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Operation Success')
		];
		//判断是否为重打分
		$model = ExamHomeworkReview::findOne(['homework_id' => $id, 'review_id' => $account_id]);
		if($model){
			$model->score = $score;
	        $model->review_at = time();
		}else{
			$model = new ExamHomeworkReview();
			$model->homework_id = $id;
			$model->review_id = $account_id;
			$model->score = $score;
	        $model->review_at = time();    
		}

		if($model->save()){
			//修改批阅状态和平均分
			return ExamHomework::updateHomeWork($id);
		}
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
	}

	//获取查询条件列表
	public function actionGetFilter()
	{
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

		$account = Common::getAccount($user_role, $account_id);
		if(!$account){
			return SendMessage::sendErrorMsg('账户不存在');
		}
		$_GET['message'] = Yii::t('teacher','Sucessfully Get List');
		return ExamHomework::getFilter($id, $account);
	}

	//查成绩
	public function actionSearchScore()
	{
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

		$class_id = Yii::$app->request->post('class_id');
		if(!$class_id){
			return SendMessage::sendErrorMsg('class_id不能为空');
		}

		$state_id = Yii::$app->request->post('state_id');
		if($state_id == NULL){
			return SendMessage::sendErrorMsg('state_id不能为空');
		}

		$sort_id = Yii::$app->request->post('sort_id');
		if(!$sort_id){
			return SendMessage::sendErrorMsg('sort_id不能为空');
		}

		$account = Common::getAccount($user_role, $account_id);
		if(!$account){
			return SendMessage::sendErrorMsg('账户不存在');
		}

		$page = (Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;

		$limit = (Yii::$app->request->post('limit')) ? Yii::$app->request->post('limit') : 10;

		$query = ExamHomework::find()->where([
			'exam_id' => $id,
			'status' => ExamHomework::STATUS_ACTIVE
		]);

		if($user_role == 'family'){
			$query->andwhere(['user_id' => $account->relation_id]);
		}else{
			if($class_id !== '001'){
				$ids = User::find()->where([
					'class_id' => $class_id,
					'status' => User::STATUS_ACTIVE
				])
				->indexBy('id')
				->all();
				$query->andwhere(['user_id' => array_keys($ids)]);
			}
		}

		if($state_id != '002'){
			$query->andwhere(['review_state' => $state_id]);
		}
		
		$query->offset($page * $limit)->limit($limit);

        if($sort_id != '003'){
        	$query->orderBy($sort_id);
        }
		
		

        $list = $query->all();

        $bottom = [
			'num' => $query->count(),
			'avg' => ($query->average('score')) ? $query->average('score') : 0,
		];

		$studio = Campus::findOne($account->campus_id)->studio_id;
		$_GET = [
			'source' => 30,
			'studio_id' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Sucessfully Get List')
		];
		
		return [
			'list' => $list,
			'bottom' => $bottom
		];
	}

	//查成绩[通过学生名字查询]
	public function actionSearchName()
	{
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}
		
		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}

		$name = Yii::$app->request->post('name');
		if(!$name){
			return SendMessage::sendErrorMsg('name不能为空');
		}

		$ids = User::find()->where([
			'name' => $name,
			'status' => User::STATUS_ACTIVE
		])
		->indexBy('id')
		->all();

		$list = ExamHomework::findAll([
			'exam_id' => $id,
			'user_id' => array_keys($ids),
			'status' => ExamHomework::STATUS_ACTIVE
		]);

		$account = Common::getAccount('teacher', $account_id);
		if(!$account){
			return SendMessage::sendErrorMsg('账户不存在');
		}
		$studio = Campus::findOne($account->campus_id)->studio_id;
		$_GET = [
			'source' => 30,
			'studio_id' => Format::getStudio('id', $account->campus_id),
			'message' => Yii::t('teacher','Sucessfully Get List')
		];
		
		return [
			'list' => $list
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

    	$model = Exam::findOne($id);
    	switch ($type) {
			case $modelClass::ICON_TYPE_RELEASE_ED:
				$model->release_state = $modelClass::RELEASE_STATE_ED;
				break;
			
			case $modelClass::ICON_TYPE_RELEASE_CANCEL:
				$model->release_state = $modelClass::RELEASE_STATE_NOT_YET;
				break;

			case $modelClass::ICON_TYPE_DELETE:
				$model->status = $modelClass::STATUS_DELETED;
				break;
		}
		$_GET['message'] = ($model->save()) ? Yii::t('teacher', '操作成功!') : Yii::t('teacher', '操作失败!');
		return [];
	}

	//更改阅卷状态
	public function actionReviweHandle()
	{
		$modelClass = $this->modelClass;

		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

    	$model = Exam::findOne($id);
    	$isset = ExamHomework::findAll([
			'exam_id' => $id,
			'status' => ExamHomework::STATUS_ACTIVE,
			'review_state' => ExamHomework::REVIEW_STATE_NOT_YET
		]);
    	if($isset){
    		$_GET['message'] = Yii::t('teacher', '有作品未批阅,不能发布成绩!');
			return [];
  		}
  		if($model->review_state == Exam::REVIEW_STATE_ED){
  			$_GET['message'] = Yii::t('teacher', '成绩已发布,无需再次操作');
			return [];
  		}
  		$model->review_state = Exam::REVIEW_STATE_ED;
  
		$_GET['message'] = ($model->save()) ? Yii::t('teacher', '操作成功!') : Yii::t('teacher', '操作失败!');
		return [];
	}

	//上传考试图片
    public function actionUpload()
    {
		$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}
		
		$account = Common::getAccount('teacher', $account_id);
		if(!$account){
			return SendMessage::sendErrorMsg('账户不存在');
		}
		$studio = Campus::findOne($account->campus_id)->studio_id;
		
		if(!$_FILES){
			return SendMessage::sendErrorMsg('上传文件为空');
		}

		$_GET['message'] = Yii::t('teacher','Upload Success');

		$image = Upload::pic_upload($_FILES, $studio, 'exam', 'image')['image'];
		$size = Yii::$app->params['oss']['Size']['320x320'];
        return [
        	'url' => Oss::getUrl($studio, 'exam', 'image', $image).$size,
        	'image_name' => $image
        ];
    }

    //上传作业
    public function actionUploadHomework()
    {
    	$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}

    	$account_id = Yii::$app->request->post('account_id');
		if(!$account_id){
			return SendMessage::sendErrorMsg('account_id不能为空');
		}
		
		$account = Common::getAccount('student', $account_id);
		if(!$account){
			return SendMessage::sendErrorMsg('账户不存在');
		}

		$studio = Campus::findOne($account->campus_id)->studio_id;
		
		if(!$_FILES){
			return SendMessage::sendErrorMsg('上传文件为空');
		}

		//判断是否是第一次上传
		$isset = ExamHomework::findOne(['exam_id' => $id, 'user_id' => $account_id, 'status' => ExamHomework::STATUS_ACTIVE]);
		if($isset){
			$isset->image = Upload::pic_upload($_FILES, $studio, 'exam', 'homework')['image'];
			if($isset->save()){
				$_GET['message'] = Yii::t('teacher','Upload Success');
				return [];
			}
		}else{
			$model = new ExamHomework();
			$model->exam_id = $id;
			$model->user_id = $account_id;
			$model->image = Upload::pic_upload($_FILES, $studio, 'exam', 'homework')['image'];
			if($model->save()){
				$_GET['message'] = Yii::t('teacher','Upload Success');
				return [];
			}
		}
		
		return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
    }


    protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}