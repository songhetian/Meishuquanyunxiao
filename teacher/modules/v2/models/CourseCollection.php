<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use components\Oss;
	use common\models\Format;

	class CourseCollection extends \common\models\CourseCollection {

		public function beforeSave($insert){

			if(parent::beforeSave($insert)){
		    	if($this->isNewRecord){
		    		if(!$this->Check()){
				    	$this->addError('material_id', '该课件已经收藏');
				        return false;
				    }
				}
				return true;
			}else{
				return false;
			}
		}
	   public function fields(){

	   	   $fields = parent::fields();

	   	   return $fields;
	   }

	   public function Check() {
		   	if(self::findOne(['material_id'=>$this->material_id,'admin_id'=>$this->admin_id,'status'=>10])) {
		   		return false;
		   	}else{
		   		return true;
		   	}
	   }
    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'teacher_id'])->alias('admins');
    }
    public function getMaterials()
    {
        return $this->hasOne(CourseMaterial::className(), ['id' => 'material_id'])->alias('materials');
    }
    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id'])->alias('users');
    }

	}

?>