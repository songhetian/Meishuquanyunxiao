<?php 
	namespace teacher\modules\v2\models;

	use Yii;
	use common\models\Format;

	class CodeCategory extends \common\models\Category
	{
		public function fields()
		{
			$fields = parent::fields();
			unset(
				$fields['type'],
				$fields['pid'],
				$fields['level'],
				$fields['color'],
				$fields['priority'],
				$fields['created_at'],
				$fields['updated_at'],
				$fields['status']
				);
			return $fields;
		}
	public static function getName($string) {

		$name = [];

		$campus = Format::explodeValue($string);

		$list = self::find()
				  ->select('name')
				  ->where(['id'=>$campus])
				  ->asArray()
				  ->all();

		return Format::implodeValue(array_column($list, 'name'));

	}
	    public function getCategorys()
	    {
	        return $this->hasMany(self::className(), ['pid' => 'id'])->select('id,name')->alias('categorys');
	    }
	}

 ?>