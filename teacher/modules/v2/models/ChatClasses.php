<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;
	use components\Jpush;
	use common\models\Format;

	class ChatClasses extends Classes{

	   public static $user_name = '';

	   public function fields(){

	   	   $fields = parent::fields();

	   	   $fields['name'] = function () {

	   	   		return $this->name."(学生)";
	   	   };
	   	   
	   	   unset(
	   	   		$fields['id'],
	   	   		$fields['year'],
	   	   		$fields['campus_id'],
	   	   		$fields['supervisor'],
	   	   		$fields['note'],
	   	   		$fields['status'],
	   	   		$fields['class_id'],
	   	   		$fields['user_count']
	   	   );
	   	   $fields['group_users'] = function() {

	   	   		$list =  $this->students;
	   	   		$new_list = array();
	   	   		foreach ($list as $key => $value) {
	   	   			$due_time = ActivationCode::findOne(['relation_id'=>$value->admin_id,'type'=>2])->due_time;

	   	   			if($due_time >= date("Y-m-d",time())) {
	   	   				$new_list[] = $value;
	   	   			}
	   	   		}

	   	   		 return $new_list;
	   	   };

	   	   return $fields;
	   }

	   //获取老师可见班级
		public static function getTeacherClsses($admin_id,$campus_id) {

			$classes =  Format::explodeValue(Admin::findOne($admin_id)['class_id']);

			$list =  self::find()
				  ->select('id')
				  ->where(['campus_id'=>$campus_id,'status'=>self::STATUS_ACTIVE])
				  ->andFilterWhere(['id'=>$classes])
				  ->asArray()
				  ->all();
			return  array_column($list, 'id');
		}

		//获取可见班级
		public static function getClsses($admin_id,$campus_id,$name='') {

			$classes =  Format::explodeValue(Admin::findOne($admin_id)['class_id']);

			$list =  self::find()
				  ->select('classes.id')
				  ->joinwith('students')
				  ->where(['classes.campus_id'=>$campus_id,'classes.status'=>self::STATUS_ACTIVE])
				  ->andFilterWhere(['classes.id'=>$classes])
				  ->andFilterWhere(['like','students.name',$name])
				  ->asArray()
				  ->all();
			return  array_column($list, 'id');
		}

		public function getStudents() {

			return $this->hasMany(ChatUser::className(),['class_id'=>'id'])
						->select(['admin_id'=>'id','name','usersig','studio_id','image','is_image'])
						->where(['students.status'=>10])
						->andWhere(['NOT',['students.usersig'=>NULL]])
						->andWhere(['NOT',['students.name'=>'']])
						->andFilterWhere(['like','students.name',self::$user_name])
						->alias('students');
		}

	}

?>