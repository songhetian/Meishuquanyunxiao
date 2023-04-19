<?php 
	namespace teacher\modules\v2\models;

	use Yii;
	use common\models\Format;
	use yii\helpers\ArrayHelper;

	class Category extends \common\models\Category
	{
		public function fields()
		{
			$fields = parent::fields();
			$fields['category_id'] = function(){
				return $this->id;
			};

			unset($fields['id']);
			return $fields;
		}
	    public function getCategorys()
	    {
	        return $this->hasMany(self::className(), ['pid' => 'id'])->select('id,name')->alias('categorys');
	    }
	}

 ?>