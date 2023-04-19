<?php 
	
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use common\models\Format;
class CodeUser extends \common\models\User
{
	public function fields()
	{
		error_reporting(0); 
		$fields = parent::fields();

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
	    	$fields['button'] = function () {
	    		return "生成";
	    	};
	    	$fields['onClick'] = function () {
	    		return 1;
	    	};
	    };
	    $fields['user_id'] = function() {
	    	return $this->id;
	    };

	    if($this->codes){
		    $fields['surplus_time'] = function() {

		    	$int =  Format::EndTime($this->codes->due_time);

		    	if($this->codes->is_first == 0) {

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
	    $fields['user_type'] = function() {
	    	return "student";
	    };
	    $fields['class'] = function () {

	    	return Classes::findOne($this->class_id)->name;
	    };
		$fields['imageUrl'] = function () {

			if($this->image){
	            $size = Yii::$app->params['oss']['Size']['320x320'];
	            if($this->is_image){
	            	$studio = 'student';
	            }else{
	            	$studio = $this->studio_id;	
	            }
	            return Oss::getUrl($studio, 'picture', 'image', $this->image).$size;
			}else{
				return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
			}
		};

		$fields['activation'] = function () {

			if($this->codes->is_active == ActivationCode::USE_ACTIVE) {
				return true;
			}else{
				return false;
			}
		};
	    $fields['activeNum'] = function () {
			return $this->codes->code ? $this->codes->code : "未生成";
		};
        $fields['identifier'] = function () {

            return 'student'.$this->id;
        };
		$fields['grade'] = function() {

			return self::getValues('grade',$this->grade) ? self::getValues('grade',$this->grade) : "未设置";
		};
		return $fields;
	}
	
	//组合搜索条件
	public static function getSerach($admin_id) {

		$campus_id  = Format::explodeValue(Admin::findOne($admin_id)->campus_id);

		$classes = CodeClasses::getClsses($admin_id,$campus_id);

		array_unshift($classes, array('title'=>'全部班级','class_id'=>'001'));

		$citys   = City::find()->select(['title'=>'name','province'=>'id'])->where(['pid'=>0])->asArray()->all();

		array_unshift($citys, array('title'=>'全部省份','province'=>'102'));

		$actives =  array(
						array(
							'title' => '全部状态',
							'is_active' => "003"
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
							'title' => '散户学生',
							'is_active' => "004"
						),
						array(
							'title' => '已过期',
							'is_active' => 30
						)
					);

		return array(
				array(
					'type'=>'title',
					'selectedIndex' => 0,
					'data' => $classes
				),
				array(
					'type'=>'title',
					'selectedIndex' => 0,
					'data' => $citys
				),
				array(
					'type'=>'title',
					'selectedIndex' => 0,
					'data' => $actives
				)

			);
	}

	//获取课件校区和班级
	public static function CampusList($admin_id)
    {
    	$list = Admin::GetCampus($admin_id);

    	$array = array();
    	foreach ($list as $key => $value) {

    		$classes = $value->classes;
	    		if($classes) {
		    		$class_list = array();
		    		foreach ($classes as $k => $v) {
		    			$class_list[] = array(
		    					'value'=>$v['id'].'',
		    					'label'=>$v['name'],
		    				);
		    		}
		    		$array[] = array(
		    			'value' => $value->id.'',
		    			'label' => $value->name,
		    			'children' => $class_list
		    		);
    			}
    	}
    	return $array;
    }

    public static function getImage($user_id,$image) {

		if($image){
            $size = Yii::$app->params['oss']['Size']['320x320'];
            $studio = self::findOne($user_id)->studio_id;
            return Oss::getUrl($studio, 'picture', 'image', $image).$size;
		}else{
			return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
		}
    }
	//获取学生详情
	public static function getInfo($id,$admin_id) {
		$user =  self::findOne($id);

		return
			array(

			array(
			 	'name'   =>$user->name,
			 	'urlPic' => self::getImage($user->id,$user->image),
			 	'credit' => $user->credit,
			 	'user_type' => 'student',
			 	'user_id'   => $user->id
			 ),
			 array(
				array(
					array(
						'title'=>"手机号",'subtitle'=>$user->phone_number
					),
					array(
						'title'=>"性别",'subtitle'=>self::getValues('gender',$user->grade) ? self::getValues('gender',$user->gender) : "未设置"
					),
					array(
						 'id'=>$user->id,'title'=>"所在校区/班级",'subtitle'=>$user->campuses->name.','.$user->classes->name ,'des'=>'基本信息','isShowLeft'=>true,
						 'otherInfo' => self::CampusList($admin_id)
					),
				),
				array(
					array(
						'title'=>'实名认证','subtitle'=> $user->national_id ? '已认证' : '未认证'
					),
					array('title' => '真实姓名','subtitle'=>$user->name),

					array('title' => '身份证号','subtitle'=>$user->national_id,'des'=>'老师根据真实信息定制课程,安排考试,成绩查询等')
				),

				array(
					array(
						'title'=>'紧急联系电话','subtitle'=>$user->contact_phone ? $user->contact_phone : '未设置'
					),
					array(
						'title'=>'家长电话','subtitle'=>$user->contact_phone ? $user->contact_phone : '未设置','des'=>'用于联系紧急联系人'
					)
				),

				array(
					array(
						'title'=>'所在归属地','subtitle'=>self::getValues('province',$user->grade) ? self::getValues('province',$user->gender) : "未设置"
					),
					array(
						'title'=>'所在年级','subtitle'=>self::getValues('grade',$user->grade) ? self::getValues('grade',$user->gender) : "未设置"
					),
					array(
						'title'=>'所在高中','subtitle' => $user->school_name ? $user->school_name : '未设置'
					),
					array(
						'title'=>'高考年份','subtitle'=>$user->graduation_at ? $user->graduation_at : '未设置'
					)
				),

				array(
					array(
						'title'=>'激活码','subtitle'=>$user->codes->code,'logo'=>'*'
					)
				)

			)
		);
	}

    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->where(['type'=>ActivationCode::TYPE_USER])->alias('codes');
    }
}

?>