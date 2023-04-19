<?php

namespace teacher\modules\v2\models;

use teacher\modules\v2\models\Classes;
use common\models\Format;

class Campus extends \common\models\Campus
{
	public static $list;

    public function beforeSave($insert)
    {
    	return true;
    }

    public function fields()
	{
	    $fields = parent::fields();
	    $fields['campus_id'] = function() {
	    	return $this->id;
	    };

	    $fields['classes'] = function() {
	    	return $this->classes;
	    };
	    unset(
	    	$fields['id'],
	    	$fields['is_main'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status'],
            $fields['studio_id']
        );
	    return $fields;
	}

	public static function getCampus($studio_id) {

		$list =  self::find()
					 ->select('id')
					 ->where(['studio_id'=>$studio_id,'status'=>self::STATUS_ACTIVE])
					 ->asArray()
					 ->all();
		return array_column($list, 'id');
	}

	public function getClasses()
	{
		 return $this->hasMany(Classes::className(), ['campus_id' => 'id'])->where(['status'=>10])->andFilterWhere(['id' => self::$list])->select('id,name')->alias('classes');
	}

	public static function getAllAdmin($studio_id) {

		$ids =  array_column(self::find()->select('id')->where(['studio_id'=>$studio_id])->asArray()->all(),'id');
		return array_column(Admin::find()->select('id')->andFilterWhere(['or like',Format::concatField('campus_id'),Format::concatString($ids)])->andWhere(['status'=>self::STATUS_ACTIVE])->asArray()->all(),'id');
		
		

	}
}
