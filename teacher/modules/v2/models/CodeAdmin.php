<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Jpush;
use common\models\Format;
use components\Oss;
#use teacher\modules\v2\models\ActivationCode;

class CodeAdmin extends Admin
{
    public function fields()
	{
		error_reporting(0); 
	    $fields = parent::fields();

	    $fields['id'] = function () {

	    	return $this->id;
	    };
		$fields['name'] = function () {

			return $this->name ? $this->name : "无";
		};
		$fields['phone_number'] = function () {

			return $this->phone_number ? $this->phone_number : "无";
		};
	    if($this->codes) {
	    	if($this->codes->is_active == ActivationCode::USE_DELETED) {

	    		if(Format::EndTime($this->codes->due_time) >= 0) {
			    	$fields['button'] = function () {
			    		return "复制";
			    	};

			    	$fields['onClick'] = function () {
			    		return 2;
			    	};
			    }else{
			    	// $fields['button'] = function () {
			    	// 	return "续费";
			    	// };

			    	// $fields['onClick'] = function () {
			    	// 	return 4;
			    	// };
			    }
	    	}else if($this->codes->is_active == ActivationCode::USE_ACTIVE){

	    		if(Format::EndTime($this->codes->due_time) >= 0){
			    	$fields['button'] = function () {
			    		return "重新激活";
			    	};
			    	$fields['onClick'] = function () {
			    		return 3;
			    	};	
	    		}else{
			    	// $fields['button'] = function () {
			    	// 	return "续费";
			    	// };

			    	// $fields['onClick'] = function () {
			    	// 	return 4;
			    	// };
	    		}
	    	};
			$fields['code_id'] = function () {

				return $this->codes->code;
			};

	    }else{
	    	
	    	// $fields['button'] = function () {
	    	// 	return "生成";
	    	// };
	    	// $fields['onClick'] = function () {
	    	// 	return 1;
	    	// };
	    };
		$fields['imageUrl'] = function () {

			if($this->image){
	            $size = Yii::$app->params['oss']['Size']['320x320'];
	            $studio = $this->studio_id;
	            return Oss::getUrl($this->studio_id, 'picture', 'image', $this->image).$size;
			}else{
				return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
			}	
		};

		$fields['role'] = function () {
            $role = Yii::$app->authManager->getRolesByUser($this->id);
            return current($role)->description;
		};
		$fields['activation'] = function () {

			if($this->codes->is_active == ActivationCode::USE_ACTIVE) {
				return false;
			}else{
				return true;
			}
		};
		$fields['activation_string'] = function () {

			if($this->codes->is_active == ActivationCode::USE_ACTIVE) {
				return "已激活";
			}else{
				return "未激活";
			}
		};
        $fields['campusId_Rn'] = function () {
            return $this->campus_id;
        };

        $fields['identifier'] = function () {
            return 'teacher'.$this->id;
        };
		$fields['activeNum'] = function () {
			return $this->codes->code;
		};

		if($this->codes){
		    $fields['surplus_time'] = function() {

		    	$int =  Format::EndTime($this->codes->due_time);

		    	if($this->codes->is_first == 0) {

		    		$activetime = $this->codes->activetime;

		    		$activetime = ($activetime == 0) ? 1 : $activetime;

                    if($this->codes->activetime == 0.09){
                        return "30天";
                    }else{
                        return round(365 * $this->codes->activetime);
                    }

		    	}else{

		    		return ($int > 0) ? $int.'天':"已过期";
		    	}
		    };
		}
	    unset(
	    	$fields['auth_key'],
	    	$fields['password_hash'],
	    	$fields['password_reset_token'],
	    	$fields['created_at'],
	    	$fields['updated_at'],
	    	$fields['status'],
	    	$fields['is_all_visible'],
	    	$fields['admin_id'],
	    	$fields['campus_id'],
	    	$fields['category_id'],
	    	$fields['class_id'],
	    	$fields['is_main']
	    );
	    return $fields;
	}
	//获取所有老师
	public static function getTeachers($campus_id) {
		$list =  self::find()
					 ->select('id')
					 ->where(['status'=>self::STATUS_ACTIVE])
					 ->andFilterWhere(['or like',Format::concatField('campus_id'),Format::concatString($campus_id)])
					 ->asArray()
					 ->all();
		return array_column($list, 'id');
	}
    public static function getImage($user_id,$image) {

		if($image){
            $size   = Yii::$app->params['oss']['Size']['320x320'];
            $studio = self::findOne($user_id)->studio_id;
            return Oss::getUrl($studio, 'picture', 'image', $image).$size;
		}else{
			return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
		}
    }
    /**
     * [多条获取作业]
     *
     *
     *
    */
    public function getCodes()
    {
 		return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->where(['type'=>ActivationCode::TYPE_TEACHER])->alias('codes');
    }

    //  public function getAuths()
    // {
    //     return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id'])->alias('auths');
    // }
}