<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;
	use components\Jpush;
	use common\models\Format;

	class ChatClassTeacher extends Classes{

	   public static $user_name = '';
	  

	   public function fields(){

	   	   $fields = parent::fields();

	   	   $fields['name'] = function () {
	   	   		return $this->name."(老师)";
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
	   	   		$list =  $this->admins;
	   	   		return $list;
	   	   		$new_list = array();
	   	   		foreach ($list as $key => $value) {
	   	   			$due_time = ActivationCode::findOne(['relation_id'=>$value->admin_id,'type'=>1])->due_time;

	   	   			if($due_time >= date("Y-m-d",time())) {
	   	   				$new_list[] = $value;
	   	   			}
	   	   		}
	   	   		return $new_list;
	   	   };

	   	   return $fields;
	   }

		public function getAdmins() {

			return $this->hasMany(ChatAdmin::className(),['class_id'=>'id'])
						->select(['admin_id'=>'admins.id','admins.name','admins.usersig','admins.studio_id','admins.image'])
						->where(['admins.status'=>10])
						->andWhere(['NOT',['admins.usersig'=>NULL]])
						->andWhere(['NOT',['admins.name'=>'']])
						->andFilterWhere(['like','admins.name',self::$user_name])
						->alias('admins');
		}

	}

?>