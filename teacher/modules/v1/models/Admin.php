<?php

namespace teacher\modules\v1\models;

use Yii;
use components\Jpush;
use common\models\Format;
use teacher\modules\v1\models\Campus;
use teacher\modules\v1\models\Classes;

class Admin extends \backend\models\Admin
{

	public function beforeSave($insert)
	{        
        $this->campus_id   =  $this->campus_id ?   $this->campus_id : NULL;
        $this->category_id =  $this->category_id ? $this->category_id : NULL;
        $this->class_id    =  $this->class_id ?    $this->class_id : NULL;
        if ($this->isNewRecord) {
            $this->setPassword($this->password_hash);
            $this->generateAuthKey();
        }else{
            if($this->password_hash != $this->getOldAttribute('password_hash')){
                $this->setPassword($this->password_hash);
            }
        }
        return true;
	}
    public function fields()
	{
	    $fields = parent::fields();
	    $fields['admin_id'] = function() {
	    	return $this->id;
	    };
	    $fields['studio_id'] = function() {
				return $this->getStudio($this->id);
	    };
	    if(empty($this->class_id)) {
		    $fields['class_id'] = function() {
		    	return $this->getClasses($this->id,$this->campus_id);
		    };
		};

	    unset(
	    	$fields['id'],
	    	$fields['auth_key'],
	    	$fields['password_hash'],
	    	$fields['password_reset_token'],
	    	$fields['created_at'],
	    	$fields['updated_at'],
	    	$fields['status']
	    );
	    return $fields;
	}

	/*
	 *[获取课件范围]
	 *
	 *
	 *
	*/
	public static function getVisua($admin_id) {
		$list = [];
		$admin = self::findOne(['id'=>$admin_id]);
		if($admin['is_all_visible'] == self::MYSELF_VISIBLE) {
			$list =  Admin::find()->select(['admin_id'=>'id','name'])
						 ->where(['id'=>$admin_id])						
						 ->asArray()
						 ->all();
		}elseif($admin['is_all_visible'] == self::ALL_VISIBLE) {
			$campuses =  Format::concatString($admin['campus_id']);
			$list =  Admin::find()->select(['admin_id'=>'id','name'])
						 ->where(['status'=>self::STATUS_ACTIVE])
						 #->where(['<>','id',$admin_id])
						 ->andFilterWhere(['or like',Format::concatField('campus_id'),$campuses])
						 ->asArray()
						 ->all();
		}

		return $list;
	}

	//获取可见班级
	public function getClasses($admin_id,$campus_id) {
		$campuses =  Format::explodeValue($campus_id);
		$list =  Classes::find()->select('id')
					 ->where(['status'=>Classes::STATUS_ACTIVE,'campus_id'=>$campuses])
					 ->indexBy('id')
					 ->all();
		if($list) {
			$classes = array_keys($list);
			return Format::implodeValue($classes);
		}else{
			return null;
		}
	}

	//获取取studio_id
	public function getStudio($admin_id) {
		$studio = Campus::findOne(self::findOne($admin_id)->campus_id)->studio_id;

		return $studio;
	}	

    /**
     * 根据手机号查询用户
     *
     * @param string $phone_number
     * @return static|null
     */
    public static function findByPhoneNumber($phone_number)
    {
    	$qeury = static::find();
        return static::findOne(['phone_number' => $phone_number, 'status' => self::STATUS_ACTIVE]);
    }

}