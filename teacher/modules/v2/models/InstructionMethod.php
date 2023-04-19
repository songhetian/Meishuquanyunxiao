<?php 
	
	namespace teacher\modules\v2\models;

	class InstructionMethod extends \common\models\InstructionMethod
	{
		public function fields()
		{
			$fields = parent::fields();
			$fields['method_id'] = function (){
				return $this->id;
			};
			unset($fields['id']);
			return $fields;
		}
	}

 ?>