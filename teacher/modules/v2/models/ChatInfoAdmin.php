<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;
	use components\Jpush;
	use common\models\Format;

	class ChatInfoAdmin extends \backend\models\Admin{

	   public function fields(){

	   	   $fields = parent::fields();

	   	   $fields['admin_id'] = function() {
	   	   		return $this->id;
	   	   };

	   	   $fields['province'] = function() {
	   	   		return $this->citys->name ? $this->citys->name : '未设置';
	   	   };
	   	   $fields['role'] = function () {
	   	   		return $this->auths->rbacs->description;
	   	   };
	   	   $fields['user_role'] = function() {
	   	   		return "teacher";
	   	   };
	       $fields['pic_url'] = function () {
	            $size = Yii::$app->params['oss']['Size']['320x320'];
	            $studio = Campus::findOne(self::findOne($this->id)->campus_id)->studio_id;
	            if($this->image){
	                return Oss::getUrl($studio, 'picture', 'image', $this->image).$size;
	            }else{
	                return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
	            }
	       };
	       $fields['expired_at'] = function() {
	       		return $this->codes->due_time;
	       };
	       $fields['created_at'] = function () {
	       		return date("Y-m-d",$this->created_at);
	       };

           $fields['identifier'] = function () {
             return 'teacher'.$this->id;
           };
	   	   unset(
	   	   		 $fields['campus_id'],
	   	   		 $fields['image'],
	   	   		 $fields['id'],
	   	   		 $fields['category_id'],
	   	   		 $fields['class_id'],
	   	   		 $fields['is_all_visible'],
	   	   		 $fields['is_main'],
	   	   		 $fields['auth_key'],
	   	   		 $fields['password_reset_token'],
	   	   		 $fields['updated_at'],
	   	   		 $fields['status'],
	   	   		 $fields['expert_category'],
	   	   		 $fields['gender'],
	   	   		 $fields['password_hash'],
	   	   		 $fields['code_number'],
	   	   		 $fields['token_value'],
	   	   		 $fields['campusId_Rn'],
	   	   		 $fields['is_code_create']

	   	   	);
	   	   return $fields;
	   }

    //获取过期时间
    public function getExpire($time,$year) {
       $year = floor($year);
       return strtotime("+$year years" ,$time);
    }


    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->where(['type'=>1])->alias('codes');
    }

    public function getCitys()
    {
        return $this->hasOne(City::className(),['id'=>'province'])->alias('citys');
    }

    public function getAuths()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id'])->alias('auths');
    }
	}
?>