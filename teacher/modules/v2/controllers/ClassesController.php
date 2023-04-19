<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Format;
use common\models\Classes;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v1\models\UserClass;
use teacher\modules\v1\models\SendMessage;
class ClassesController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Classes';

	/**
	 * [返回班级列表]
	 *  @param $admin_id 班主任id
	 *  @return 班级id-name列表
	 */
	public function actionList()
	{
		$admin_id = Yii::$app->request->post('admin_id');
		if(!$admin_id){
			return SendMessage::sendErrorMsg('admin_id不能为空');
		}

		$Admin     =  Admin::findOne($admin_id);
		
		$item_name =  $Admin->auths->item_name;

		$pid = substr($item_name,-3);

		if(in_array($Admin->codes->studio_id, Yii::$app->params['Studio'])){
			if(!in_array($pid,Yii::$app->params['Shenfen'])) {

				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}
		}
		//查找老师可见班级
		$admin      = Admin::findOne($admin_id);
		if(Yii::$app->request->post('campus_id')){
			$campus_id = Yii::$app->request->post('campus_id');
		}else{
			$campus_id = Format::explodeValue($admin['campus_id']);
		}
		$class_id   = Format::explodeValue($admin['class_id']);

		$modelClass = $this->modelClass;
		$res['info'] = $modelClass::find()->where(['status' => $modelClass::STATUS_ACTIVE])
					   ->andFilterWhere(['campus_id' => $campus_id])
					   ->andFilterWhere(['id' => $class_id])
					   ->all();
		$supervisors = Admin::getVisua($admin_id);
		foreach ($supervisors as $value) {
			$key = $value['admin_id'];
			$supervisor = [];
        	$supervisor[$key]['key'] = $key;
        	$supervisor[$key]['value'] = $key;
        	$supervisor[$key]['label'] = $value['name'];
        	$res['supervisors'][] = $supervisor[$key];
		}
		$_GET['message'] = Yii::t('teacher','Sucessfully Class List');
		return $res;
	}

	public function actionCreate()
	{
		$model = new Classes();
		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Create Class Success');
			return $this->findModel($model->id);
		}else{
			return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
		}
	}

	//更新班级信息
    public function actionUpdate()
    {
    	$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}
        $model = $this->findModel($id);
        if($model){
            $model->load(Yii::$app->getRequest()->getBodyParams(), '');
            if ($model->save()) {
                $_GET['message'] = Yii::t('teacher', 'Update Class Success');
                return $model;
            } else {
                return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
            }
        }
        return SendMessage::sendErrorMsg(Yii::t('teacher', 'Class Not Exist')); 
    }

	public function actionDelete()
	{
		$id = Yii::$app->request->post('id');
		if(!$id){
			return SendMessage::sendErrorMsg('id不能为空');
		}
		$model = $this->findModel($id);
        if($model->updateStatus()){
            return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Delete Class Success'));
        }
	}


	/**
	 * [返回班级学生作业信息]
	 * @param $class_id 班级id
	 * @return mixed 学生 作业
	 *
	*/
	public function actionInformationTest($admin_id='',$class_id,$course_id='',$limit=10,$page=0)
	{
		if($admin_id == 0) {

			$admin_id = '';
		}

		$offset = $page*$limit;

		$students = UserClass::getClassList($class_id);

		$list = \common\models\UserHomework::find()
							->select(['user_id'])
							->andWhere(['user_id'=>$students,'status'=>UserClass::STATUS_ACTIVE])
							->andFilterWhere(['course_id'=>$course_id])
							->orderBy('video DESC,score DESC')
							->asArray()
							->all();

		$ids =  array_unique(array_column($list, 'user_id'));

		
		if($admin_id){
			array_unshift($ids, $admin_id);
		}
		
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		$homeworks =  User::find()
							->select('id,name')
							->where(['id'=>$ids])
							->asArray()
							->all();


		foreach ($homeworks as $key => $value) {
			$homeworks[$key]['user_id'] =  $value['id'];
			$homeworks[$key]['homeworks'] =$this->homework($value['id'],$course_id);
		}
		if($admin_id) {
			foreach ($homeworks as $key => $value) {
				if($value['id'] == $admin_id) {
					unset($homeworks[$key]);
					array_unshift($homeworks, $value);
				}
			}
		}
		$homeworks = array_slice($homeworks,$offset,$limit);
		return $homeworks;
		// if(in_array($admin_id, $admin_list)) {
		// 	return $homeworks;
		// }else{
		// 	return $homeworks;
		// }
	}

	//返回学生作业列表
	public function homework($user_id,$course_id='') {
		return  \teacher\modules\v1\models\UserHomeWork::find()
							->where(['user_id'=>$user_id,'status'=>UserClass::STATUS_ACTIVE])
							->andFilterWhere(['course_id'=>$course_id])
							->orderBy('video DESC,score DESC')
							->all();
	}

	protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>