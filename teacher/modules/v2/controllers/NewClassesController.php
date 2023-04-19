<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use backend\models\Admin;
use common\models\User;
use common\models\Format;
use common\models\Classes;
use common\models\ClassesGive;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\NewCampus;
use teacher\modules\v2\models\CodeAdmin;
use teacher\modules\v2\models\NewClasses;
use teacher\modules\v2\models\SendMessage;

class NewClassesController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\NewClasses';

	/**
	 * [创建售卖课程]
	 */
	public function actionCreate() {

		#return $this->user_id.'-'.$this->user_role.'-'.$this->studio_id;
		//判断何时是否为183
		if(!in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $this->user_role != 10) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));

		}

		$item_name =  CodeAdmin::findOne($this->user_id)->auths->item_name;

		$pid = substr($item_name,-3);

		//判断是否为校长或者管理员身份
		if(!in_array($pid,Yii::$app->params['Shenfen'])) {
			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
		}

		$model             = new NewClasses();
		
		$model->setScenario('create');
		
		$model->type       = 2;
		
		$model->campus_id  = 370;
		
		$model->supervisor =  $this->user_id;
		
		$model->year       =  date("Y"); 

		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Create Class Success');
			return $this->findModel($model->id);
		}else{

			return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
		}
	}


	/**
	 * @Author    THS
	 * @DateTime  2020-12-08
	 * @copyright [获取详情]
	 * @license   [license]
	 * @version   [2.0]
	 * @param     [$class_id 班级id]
	 * @return    [type]
	 */
	public function actionGetInfo($class_id) {

		NewClasses::$visitor_id = $this->user_id;
		NewClasses::$role = $this->user_role;
		
		$_GET['message'] = Yii::t('teacher','Sucessfully Get Date');

		return $this->findModel($class_id);
	
	}

	/**
	 * @Author    THS
	 * @DateTime  2020-12-08
	 * @copyright [班级修改]
	 * @license   [license]
	 * @version   [2.0]
	 * @return    [type]
	 */
	public function actionUpdate() {

		if(!in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $this->user_role != 10) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));

		}

		$item_name =  CodeAdmin::findOne($this->user_id)->auths->item_name;

		$pid = substr($item_name,-3);

		//判断是否为校长或者管理员身份
		if(!in_array($pid,Yii::$app->params['Shenfen'])) {
			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
		}

		$id = Yii::$app->request->post('class_id');

		$model = $this->findModel($id);

		if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->save()) {
			$_GET['message'] = Yii::t('teacher', 'Update Success');
			return $this->findModel($model->id);
		}else{

			return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
		}

	}

	/**
	 * @Author    THS
	 * @DateTime  2020-12-08
	 * @copyright 删除课程
	 * @license   [license]
	 * @version   [version]
	 * @param     [type]
	 * @return    [type]
	 */
	public function actionDelete($class_id) {

		if(!in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $this->user_role != 10) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));

		}

		$item_name =  CodeAdmin::findOne($this->user_id)->auths->item_name;

		$pid = substr($item_name,-3);

		//判断是否为校长或者管理员身份
		if(!in_array($pid,Yii::$app->params['Shenfen'])) {

			return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
		}

		$model = $this->findModel($class_id);

		$model->status = 0;

		if($model->save()) {

			return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Delete Success'));
		}else{
			return SendMessage::sendErrorMsg(Yii::t('teacher', 'Handle Field'));
		}
	}

	/**
	 * @Author    THS
	 * @DateTime  2020-12-08
	 * @copyright 获取列表
	 * @license   [license]
	 * @version   [version]
	 * @return    [type]
	 */
	public function actionGetList() {

		$admin_id = $this->user_id;

		switch ($this->user_role) {
			case 10:
				$user = Admin::findOne($admin_id);
				$old_campus = Format::explodeValue($user->campus_id);
				$class_id  = Format::explodeValue($user->class_id);
				break;
			case 20:
				$user = User::findOne($admin_id);
				$old_campus = Format::explodeValue($user->campus_id);
				$class_id  = Format::explodeValue($user->class_id);
				break;
			case 30:
				$old_campus = array();
				break;
		}
		
		$list = array_column(NewCampus::find()->select('id')->where(['studio_id'=>$this->studio_id,'status'=>10])->asArray()->all(),'id');
		$campus_id =  array_unique(array_merge($old_campus,$list));
		
		NewClasses::$visitor_id = $this->user_id;
		NewClasses::$role = $this->user_role;

		switch ($this->user_role) {
			case 10:
				$campuses  =  array();

				foreach ($campus_id as  $key=>$value) {	
					
				 	$a = NewCampus::find()->where(['id'=>$value])->one();

				 	$campuses[$key] = $a->toArray();

				 	//收费班级
					$charge = NewClasses::find()->where(['campus_id'=>$value,'type'=>2,'status'=>10])->orderBy("created_at DESC")->all(); 

					$free = array();
					
					if($old_campus) {
						//免费班级
						$free  =  NewClasses::find()->where(['campus_id'=>$value,'type'=>1,'status'=>10])->andFilterWhere(['id'=>$class_id])->all();
					}

					$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
					
					$campuses[$key]['classes'] = array_merge($charge,$free);
				}
				$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
				return $campuses;
				break;
			case 20:
				$campuses  =  array();

				foreach ($campus_id as  $key=>$value) {	
					
				 	$a = NewCampus::find()->where(['id'=>$value])->one();

				 	$campuses[$key] = $a->toArray();

				 	//收费班级
					$charge = NewClasses::find()->where(['campus_id'=>$value,'type'=>2,'status'=>10])->orderBy("created_at DESC")->all(); 

					//免费班级
					$free  =  NewClasses::find()->where(['campus_id'=>$value,'type'=>1,'id'=>$class_id,'status'=>10])->all();

					$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');

					$campuses[$key]['classes'] = array_merge($charge,$free);
				}
				$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
				return $campuses;
				break;
			case 30:
					$campuses  =  array();

					foreach ($campus_id as  $key=>$value) {	
						
					 	$a = NewCampus::find()->where(['id'=>$value])->one();

					 	$campuses[$key] = $a->toArray();

						$campuses[$key]['classes'] =  NewClasses::find()->where(['campus_id'=>$value,'type'=>2,'status'=>10])->orderBy("created_at DESC")->all();  
					}
					$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
					return $campuses;
				break;
		}

	}

	public function actionGetGive(){
		$give = ClassesGive::find()->where(' 1 = 1 ')->asArray()->all();
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		$arr = array();
		foreach($give as $key=>$value){
			$temp['tip_id'] = (int)$value['id'];
			#$temp['value'] = (int)$value['values'];
			$temp['title'] = $value['title'];
			#$temp['type'] = $value['type'];
			$arr[] = $temp;
		}
		return $arr;
	}
	/**
	 * @Author    THS
	 * @DateTime  2020-12-28
	 * @copyright [获取价格列表]
	 * @license   [license]
	 * @version   [2.0]
	 * @return    [array]
	 */
	public function actionGetPrice($is_ios = 0) {


		if($is_ios == 1){
			$list =  Yii::$app->params['IosPrice'];
		}else{
			$list =  Yii::$app->params['AndroidPrice'];
		}
		

		$price_list  = array();

		foreach ($list as $key => $value) {

			if($is_ios == 1) {
				$price_list[] = array(
					'price'    => $key,
					'price_id' => "com.yunxiao.meishuquancontent".$value,
					'info'     => $value.'元'
				);
			}else{
				$price_list[] = array(
					'price_id' => $key,
					'info'     => $value.'元'
				);
			}
		}
		$_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
		
		return $price_list;
	}

	/**
	 * @Author    田鹤松
	 * @DateTime  2021-01-21
	 * @copyright [获取能否编辑的状态]
	 * @license   [license]
	 * @version   [version]
	 * @param     [type]      $class_id [description]
	 * @return    [type]                [description]
	 */
	public function actionGetEditorStatus($class_id) {

		if($this->user_role != 10) {
			$_GET['message'] = "获取状态成功";

			return false;
		}

		if(!Classes::findOne($class_id)) {

			$_GET['message'] = "获取状态成功";
			return false;
		}
		
		$flag = true;
		//收费课程验证
		$ClassType = Classes::findOne($class_id)->type;
		
		if(in_array($this->studio_id, Yii::$app->params['NoYanZheng']) && $ClassType == 2){

			$TeacherList =	Classes::getEditorList($class_id);

			$item_name   =  CodeAdmin::findOne($this->user_id)->auths->item_name;
			
			$pid         = substr($item_name,-3);


			$flag = (in_array($pid,Yii::$app->params['Shenfen']) || in_array($this->user_id,$TeacherList)) ? true : false;

		}else{

			$model = CodeAdmin::findOne($this->user_id);

			$flag  = ($model->is_create == 1) ? true : false;
		}

		$_GET['message'] = "获取状态成功";

		return $flag;
	}

	protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}
?>