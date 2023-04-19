<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;

	class ChatInfoFamily extends \common\models\Family{

	   public function fields(){

	   	   $fields = parent::fields();
	   	   $fields['admin_id'] = function() {
	   	   		return $this->id;
	   	   };
	       $fields['pic_url'] = function () {
	            $size = Yii::$app->params['oss']['Size']['320x320'];
	            if($this->image){
	                return Oss::getUrl('family', 'picture', 'image', $this->image).$size;
	            }else{
	                return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
	            }
	       };
	       $fields['province'] = function() {

	       		return $this->citys->name ? $this->citys->name : '未设置';
	       };

	       $fields['child'] = function() {
	       		return [
	       			'user_role' => 'student',
	       			'name'  => $this->childs->name ? $this->childs->name : '未关联学生',
	       			'admin_id' => $this->childs->id
	       		];
	       };
	   	   $fields['user_role'] = function() {
	   	   		return "family";
	   	   };
	       $fields['identifier'] = function () {
	             return 'family'.$this->id;
	       };
	   	   unset(
	   	   		 $fields['campus_id'],
	   	   		 $fields['gender'],
	   	   		 $fields['id'],
	   	   		 $fields['image'],
	   	   		# $fields['usersig'],
	   	   		 $fields['created_at'],
	   	   		 $fields['updated_at'],
	   	   		 $fields['status'],
	   	   		 $fields['studio_id'],
	   	   		 $fields['token_value'],
	   	   		 $fields['campusId_Rn'],
	   	   		 $fields['student_name'],
	   	   		 $fields['is_band'],
	   	   		 $fields['relation_id']
	   	   	);
	   	   return $fields;
	   }
	    public function getCitys()
	    {
	        return $this->hasOne(City::className(),['id'=>'province'])->alias('citys');
	    }

	    public function getChilds()
	    {
	        return $this->hasOne(User::className(),['id'=>'relation_id'])->alias('childs');
	    }

	}

?>