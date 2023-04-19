<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;
	use components\Jpush;
	use common\models\Format;

	class ChatFamily extends Family{

	   public function fields(){

	   	   $fields = parent::fields();

	   	   unset($fields['phone_number'],
	   	   		 $fields['campus_id'],
	   	   		 $fields['gender'],
	   	   		 $fields['province'],
	   	   		 $fields['token_value'],
	   	   		 $fields['campusId_Rn'],
	   	   		 $fields['student_name'],
	   	   		 $fields['is_band'],
	   	   		 $fields['relation_id']
	   	   	);
	   	   return $fields;
	   }
	    //根据画室获取家长
	    public static function getAll($studio_id,$student_id='',$name='') {
	        return self::find()
	                  ->where(['studio_id'=>$studio_id,'status'=>10])
	                  ->andWhere(['NOT',['usersig'=>NULL]])
	                  ->andFilterWhere(['relation_id'=>$student_id])
	                  ->andFilterWhere(['like','name',$name])
	                  ->orderBy('id DESC')
	                  ->all();
	    }
	}

?>