<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Chat;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%activation_code}}".
 *
 * @property integer $id
 * @property string $code
 * @property integer $type
 * @property integer $relation_id
 * @property integer $studio_id
 * @property integer $status
 */
class ActivationCode extends \common\models\ActivationCode
{
	public function fields() {

		$fields = parent::fields();

		$fields['name'] = function () {

			return $this->admins->name ? $this->admins->name : "无";
		};

		if($this->type == self::TYPE_TEACHER) {
			$fields['role'] = function () {

				$role = Yii::$app->authManager->getRolesByUser($this->admins->id);
	            return $role[key($role)]->description;
			};
		}
		$fields['activation'] = function () {

			if($this->is_active == self::USE_ACTIVE) {
				return true;
			}else{
				return false;
			}
		};

		$fields['activeNum'] = function () {
			return $this->code;
		};

		$fields['phone'] = function () {
			return $this->admins->phone_number ? $this->admins->phone_number : "无";
		};

		$fields['imageUrl'] = function () {

			return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
		};
		unset(
			$fields['updated_at'],
			$fields['created_at'],
			$fields['status'],
			$fields['is_active'],
			$fields['studio_id'],
			$fields['type'],
			$fields['relation_id'],
			$fields['code'],
			$fields['activetime']

		);
		return $fields;
	}

	//验证验证码是否重复
	public static function check($code) {


	}
	//激活码号查找
	public static function findByNumber($code,$type) {
		$model = static::findOne(['code' => $code, 'status' => self::STATUS_ACTIVE,'type'=>$type]);
		if($model) {
			// $model->is_active = self::USE_ACTIVE;
			// $model->save();
			return $model;
		}else{
			return false;
		}
	}
	//激活码id查找
	public static function findById($code) {
		$model = static::findOne(['code' => $code, 'status' => self::STATUS_ACTIVE]);
		if($model) {
			// $model->is_active = self::USE_ACTIVE;
			// $model->save();
			return $model;
		}else{
			return false;
		}
	}
	//验证码数量
	public static function getCount($studio_id,$type,$activetime,$is_active='') {
		return self::find()
					 ->where(['studio_id'=>$studio_id,'status'=>self::STATUS_ACTIVE,'activetime'=>$activetime])
					 ->andFilterWhere(['is_active'=>$is_active])
					 ->andFilterWhere(['type'=>$type])
					 ->count('id');
	}	


	//返回激活码数量
	public static function getNumber($studio_id,$activetime,$type) {
		return self::find()
					 ->andWhere(['studio_id'=>$studio_id,'activetime'=>$activetime,'status'=>self::STATUS_ACTIVE,'type'=>$type])
					 ->count('id');
	}
	//返回校长激活码
	public static function getXiaoZhang() {
		$list =  \backend\models\Admin::find() 
						  ->select('id')
						  ->asArray()
						  ->where(['status'=>10,'is_main'=>10])
						  ->all();


		$admins =  array_column($list, 'id');

		$codes = self::find()
						->select('code')
						->asArray()
						->where(['relation_id'=>$admins])
						->all();
		
		$code =  array_column($codes, 'code');

		$user = array();
		
		$users = Yii::$app->params['NotUsers'];

		return array_merge($code,$users);

	}

}
