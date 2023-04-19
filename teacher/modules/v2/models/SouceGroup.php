<?php 	
	namespace teacher\modules\v2\models;

	use Yii;
	use common\models\Format;
	use components\Oss;
	
	class SouceGroup extends \common\models\SouceGroup
	{

    public function beforeSave($insert)
    {        
        if ($this->isNewRecord) {
        	$this->created_at = time();
        	$this->updated_at = time();
        }else{
        	$this->updated_at = time();
        }
        return true;
    }
		public function fields()
		{
		    $fields = parent::fields();

		    $fields['group_id'] = function() {
		    	return $this->id;
		    };

		    if($this->material_library_id){
			    $fields['preview'] = function () {
			    	$exp = Format::explodeValue($this->material_library_id);
			        return $this->getPreview($exp,$this->role);
			    };
			}else{
			    $fields['preview'] = function () {
			        return null;
			    };
			}


		    if($this->material_library_id){
			    $fields['preview_more'] = function () {
			    	$exp = Format::explodeValue($this->material_library_id);
			        return $this->getMorePreview($exp,$this->role);
			    };
			}else{
			    $fields['preview_more'] = function () {
			        return null;
			    };
			}
	        $fields['number'] = function () {
	        	$exp = Format::explodeValue($this->material_library_id);
	        	$exp = array_filter($exp);
	            return count($exp);
	        };

		    unset(
		    	$fields['id'],
		    	$fields['course_material_id'], 
		    	$fields['created_at'], 
		    	$fields['updated_at'], 
		    	$fields['status']
		    );
		    return $fields;
		}

		public function getPreview($exp,$role){
			if($this->type== self::TYPE_PICTURE){
				$size = Yii::$app->params['oss']['Size']['320x320'];
				$model = Picture::findOne(current($exp));
				if($role == 10){
					$studio = Admin::findOne($this->admin_id)->studio_id;
				}elseif($role == 20){
					$studio = User::findOne($this->admin_id)->studio_id;
				}
		    	$image = ($model->source == Picture::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $model->image) : $model->image;
			}else{
				$size = Yii::$app->params['oss']['Size']['475x270'];
				$model = Video::findOne(current($exp));
				$studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
	            $image = ($model->source == Video::SOURCE_LOCAL) ? Oss::getUrl($studio, 'video', 'preview', $model->preview) : $model->preview;	
			}
			return $image.$size;
		}

		public function getMorePreview($exp,$role){
			$image = array();
			if($this->type== self::TYPE_PICTURE){
				$size = Yii::$app->params['oss']['Size']['320x320'];
				$array = array_slice($exp, 0 ,4);
				foreach ($array as $key => $value) {
					$model = Picture::findOne($value);
					if($role == 10){
						$studio = Admin::findOne($this->admin_id)->studio_id;
					}elseif($role == 20){
						$studio = User::findOne($model->admin_id)->studio_id;
					}
			    	$image['img'.($key+1)] = ($model->source == Picture::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $model->image).$size : $model->image.$size;
				}
			}else{
				$size = Yii::$app->params['oss']['Size']['475x270'];
				$array = array_slice($exp, 0 ,4);

				foreach ($array as $key => $value) {
					$model =  Video::findOne($value);
					$studio = Admin::findOne($this->admin_id)->studio_id;
			    	$image['img'.($key+1)] = ($model->source == Video::SOURCE_LOCAL) ? Oss::getUrl($studio, 'video', 'preview', $model->preview).$size : $model->preview.$size;
				}
			}
			
			return $image;
		}
		//获取默认分组
		public static function getDefault($admin_id,$type,$role)
		{
			$group = self::find()
							->where(['admin_id'=>$admin_id,
										  'is_main'=>SouceGroup::IS_MAIN,
										  'role'   => $role,
										  'type'=>$type])
							->one();
			if($group) {
				return $group['id'];
			}else{
				return null;
			}
		}


		//创建默认分组
		public static function CreateDefaut($admin_id,$type,$role)
		{
			$model = new self();
			$model->admin_id = $admin_id;
			$model->name     = Yii::t('teacher','Defaut Group');
			$model->role     = $role;
			$model->is_main  = self::IS_MAIN;
			$model->type     = $type;
			$model->created_at  = time();
			$model->updated_at  = time();

			if($model->save()) {
				return Yii::$app->db->getLastInsertId();
			}else{

				return 1;
			}
		}

	    //删除多个分组
	    public static function delMore($group) {
	    	$groups = explode(',',$group);
	    	foreach ($groups as $key => $group_id) {
	    		$model = self::findOne($group_id);
	    		$model->updateStatus();
	    	}
	    }

	}
 ?>