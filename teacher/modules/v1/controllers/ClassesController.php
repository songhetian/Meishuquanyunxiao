<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use common\models\classes;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\User;
use teacher\modules\v1\models\Admin;
use common\models\Format;
use common\models\Curl;
use teacher\modules\v1\models\UserClass;

class ClassesController extends MainController
{
	public $modelClass = 'teacher\modules\v1\models\Classes';

	/**
	 * [返回班级列表]
	 *  @param $admin_id 班主任id
	 *  @return 班级id-name列表
	 */
	public function actionList($admin_id)
	{
		//查找老师课件班级
		$admin      = Admin::findOne($admin_id);
		$campus_id  = Format::explodeValue($admin['campus_id']);
		$class_id   = Format::explodeValue($admin['class_id']);
		$modelClass = $this->modelClass;
		$query = $modelClass::find()->select('id,name')
									->where(['status'=>$modelClass::STATUS_ACTIVE])
									->andFilterWhere(['campus_id'=>$campus_id])
									->andFilterWhere(['id'=>$class_id]);
		$_GET['message'] = Yii::t('teacher','Sucessfully Class List');
        return new ActiveDataProvider([
            'query' => $query
        ]);
	}
	/**
	 * [返回班级学生作业信息]
	 * @param $class_id 班级id
	 * @return mixed 学生 作业
	 *
	*/
	public function actionInformation($class_id,$course_material_id='')
	{
		error_reporting(E_ALL^E_NOTICE^E_WARNING);
		if(!$course_material_id){
			$list =  User::find()->select('id,name')
								  ->where(['class_id'=>$class_id,'status'=>User::STATUS_ACTIVE])->all();

			$_GET['message'] = Yii::t('teacher','Sucessfully List');

			foreach ($list as $key => $value) {
				if(!$value->homeworks){
				  unset($list[$key]->homeworks);
				}
			}
			return array_values($list); 
	    }else{
			$list = Curl::metis_file_get_contents(
	            "http://api.teacher.meishuquanyunxiao.com/v1/classes/information?class_id={$class_id}"
	        );

			foreach ($list as $key => $value) {
				foreach ($value->homeworks as $k => $v) {
					if($v->course_material_id != $course_material_id)
					{
						unset($list[$key]->homeworks[$k]);	
					}

				}
			}	
			$_GET['message'] = Yii::t('teacher','Sucessfully List');

			foreach ($list as $key => $value) {
				$list[$key]->homeworks = array_values($value->homeworks);
				if(!$value->homeworks){
				  	unset($list[$key]);
				}
			}

	        return array_values($list);
	    }
	}

	/**
	 * [返回班级学生作业信息]
	 * @param $class_id 班级id
	 * @return mixed 学生 作业
	 *
	*/
	public function actionInformationTest($class_id,$course_id='',$limit=5,$page=0)
	{
		$students = UserClass::getClassList($class_id);

		$list = \common\models\UserHomework::find()
							->select(['user_id'])
							->where(['user_id'=>$students,'status'=>UserClass::STATUS_ACTIVE])
							->andFilterWhere(['course_id'=>$course_id])
							->orderBy('video DESC,score DESC')
							->asArray()
							->all();

		$ids =  array_unique(array_column($list, 'user_id'));

		$offset = $page*$limit;
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		$homeworks =  User::find()
							->select('user.id,name')
							->where(['user.id'=>$ids])
							->joinWith('homeworks')
							->andFilterWhere(['homeworks.course_id'=>$course_id])
							->offset($offset)
							->limit($limit)
							->asArray()
							->all();
		foreach ($homeworks as $key => $value) {
			$homeworks[$key]['homeworks'] =$this->homework($value['id'],$course_id);
		}

		return $homeworks;
	}

	//返回学生作业列表
	public function homework($user_id,$course_id='') {
		return  \teacher\modules\v1\models\UserHomeWork::find()
							->where(['user_id'=>$user_id,'status'=>UserClass::STATUS_ACTIVE])
							->andFilterWhere(['course_id'=>$course_id])
							->orderBy('video DESC,score DESC')
							->all();

	}


}
?>