<?php 
	
namespace teacher\modules\v1\models;

use Yii;
use teacher\modules\v1\models\UserHomeWork;
use common\models\Campus;
use components\Oss;
use backend\models\Admin;
use components\Spark;

class User extends \common\models\User
{
	public function fields()
	{
	    $fields = parent::fields();
	    $fields['user_id'] = function() {
	    	return $this->id;
	    };
	    unset(
	    	$fields['id']
	    );
	   	$fields['homeworks'] = function (){
	   		$array = $this->homeworks;
	   		foreach ($array as $key => $value) {
	   			if(!empty($value['image'])){
	   				$image = $value['image'];
		    		$size = Yii::$app->params['oss']['Size']['350x350'];
		    		$studio = Campus::findOne(User::findOne($this->id)->campus_id)->studio_id;
		            $array[$key]['image'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            $size = Yii::$app->params['oss']['Size']['original'];
		            $array[$key]['image_original'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            	$array[$key]['evaluator']  = Admin::findOne($value['evaluator'])['name'];
		            $array[$key]['created_at'] = date("Y/m/d",$value['created_at']);
		            $array[$key]['user_name']  = $this->name;
	   			}
	   			if(!empty($value['comment_image'])){
	   				$comment_image = $value['comment_image'];
		    		$size = Yii::$app->params['oss']['Size']['350x350'];
		    		$studio = Campus::findOne(User::findOne($this->id)->campus_id)->studio_id;
		            $array[$key]['comment_image'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            $size = Yii::$app->params['oss']['Size']['original'];
		            $array[$key]['comment_image_original'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            	$array[$key]['evaluator']  = Admin::findOne($value['evaluator'])['name'];
	   			}
	   			if(!empty($value['video'])){
	   				$array[$key]['video']  = (Object)[
	                    'cc_id' => $value['video'],
	                    'charging_option' => $value['charging_option'],
	                    'duration' =>  Spark::getDuration($value['video'], $value['charging_option']*10),
                	];
	   			}
	   			if(!empty($value['course_id'])) {
	   				$array[$key]['course_id'] = $value['course_id'];
	   			}
	   		}

	   		return $array;
	    };
	    return $fields;
	}
	//获取班级学生id集合
	public static function getUsers($class_id) {

		$users = self::find()->select('id,name')->where(['class_id'=>$class_id,'status'=>self::STATUS_ACTIVE])->asArray()->all();

		return array_column($users,'id');
	}

	/**
	 * [多条获取作业]
	 *
	 *
	 *
	*/
	public function getHomeworks()
	{
		return $this->hasMany(UserHomeWork::className(),['user_id'=>'id'])->select(['homework_id'=>'id','image','comments','score','evaluator','created_at','course_material_id','video','comment_image','charging_option','course_id'])->orderby('video DESC,score DESC')->asArray()->alias('homeworks');
	}


}

?>