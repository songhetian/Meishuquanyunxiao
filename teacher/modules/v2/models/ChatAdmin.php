<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;
	use components\Jpush;
	use common\models\Format;

	class ChatAdmin extends Admin{

	   public function fields(){

	   	   $fields = parent::fields();
	   	   
	   	   unset($fields['phone_number'],
	   	   		 $fields['campus_id'],
	   	   		 $fields['expert_category'],
	   	   		 $fields['province'],
	   	   		 $fields['gender'],
	   	   		 $fields['password_hash'],
	   	   		 $fields['code_number'],
	   	   		 $fields['token_value'],
	   	   		 $fields['campusId_Rn'],
	   	   		 $fields['is_code_create']

	   	   	);

	   	   return $fields;
	   }

	   /*
		*
		*
		*
		*
	   */
	    public static function getSanHu($studio_id) {

	        	$users =  self::find()
		                        ->select('id')
		                        ->where(['studio_id'=>$studio_id,'status'=>10,'campus_id'=>NULL])
		                        ->orWhere(['studio_id'=>$studio_id,'status'=>10,'campus_id'=>''])
		                        ->andWhere(['NOT',['usersig'=>NULL]])
		                        ->asArray()
		                        ->all();

	        	return array_column($users, 'id');

	    }

	       /*
	     *[获取课件范围]
	     *
	     *
	     *
	    */
	    public static function getTeacherByStudio($studio_id) {
	        $list = [];

	        $campus = Campus::find()
	        			->select('id')
	        			->where(['studio_id'=>$studio_id,'status'=>10])
	        			->indexBy('id')
	        			->all();
	        $campus_id = implode(',', array_keys($campus));

            $campuses =  Format::concatString($campus_id);

            $list =  Admin::find()->select('id')
                         ->where(['status'=>self::STATUS_ACTIVE,'is_chat'=>1])    
                         ->andWhere(['NOT',['usersig'=>NULL]])
                         ->andFilterWhere(['or like',Format::concatField('campus_id'),$campuses])
                         ->indexBy('id')
                         ->all();
	        return array_keys($list);
	    }
	}
?>