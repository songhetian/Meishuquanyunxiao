<?php 

	namespace teacher\modules\v2\models;

	use Yii;
	use backend\models\ActiveRecord;

	class Rbac extends \backend\models\Rbac {

		public function fields() {

			$fields = parent::fields();

			return $fields;
		}

	    public function beforeValidate()
	    {
	        return true;
	    }

	    public static function getRolesApi1($studio_id,$type)
	    {
	        $model = static::find()
	        	->select(['name','title'=>'description'])
	            ->where([
	                'studio_id' => $studio_id,
	                'type' => self::TYPE_ROLE, 
	                'status' => self::STATUS_ACTIVE
	            ])
	            ->asArray()
	            ->orderBy('created_at')
	            ->all();

	        if($type == 1){
	        	array_unshift($model,array('name'=>"001",'title'=>'全部职务'));
	        }

	        return $model;
	    }



	    public static function getRolesApi($studio_id,$type)
	    {
	        $model = static::find()
	        	->select(['name','title'=>'description'])
	            ->where([
	                'studio_id' => $studio_id,
	                'type' => self::TYPE_ROLE, 
	                'status' => self::STATUS_ACTIVE
	            ])
	            ->asArray()
	            ->orderBy('created_at')
	            ->all();

	        if($type == 1){
	        	array_unshift($model,array('name'=>"001",'title'=>'全部职务'));

	        	array_pop($model); 
	        }


	       return  array(
		        	array('type'=>'Title',
		        		   'selectedIndex' => 0,
		        		   'data' => $model
		        	),
					array(
					   'type'=>'Subtitle',
	        		   'selectedIndex' => 0,
						'data' =>array( 
							array(
							'title' => '全部状态',
							'is_active' => "002"
							),
							array(
								'title' => '未激活',
								'is_active' => 20
							),
							array(
								'title' => '已激活',
								'is_active' => 10
							),
							array(
								'title' => '散户老师',
								'is_active' => 40
							),
							array(
								'title' => '已过期',
								'is_active' => 30
							)
						)
					)
		        );
	    }
	}
 ?>