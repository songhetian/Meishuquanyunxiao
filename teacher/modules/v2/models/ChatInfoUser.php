<?php 
	
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use common\models\Format;

class ChatInfoUser extends \common\models\User
{
	public function fields()
	{
		$fields = parent::fields();
        
       $fields['admin_id'] = function() {
            return $this->id;
       };

        $fields['class_id'] = function () {
            return Classes::findOne($this->class_id)->name;
        };
        $fields['province'] = function() {
            return City::findOne($this->province)->name ? City::findOne($this->province)->name : '未设置';
        };
        $fields['grade'] = function() { 
            return self::getValues('grade',$this->grade) ? self::getValues('grade',$this->grade) :'未设置';
        };
        $fields['school_name'] = function() { 
            return $this->school_name ? $this->school_name :'未设置';
        };
        $fields['total_score'] = function() { 
            return $this->total_score ? $this->total_score :'未设置';
        };
        $fields['graduation_at'] = function() { 
            return $this->graduation_at ? $this->graduation_at.'年' :'未设置';
        };
        $fields['total_score'] = function() { 
            return $this->total_score ? $this->total_score :'未设置';
        };

        $fields['code_number'] = function () {
            if($this->codes){
                $int =  Format::EndTime($this->codes->due_time);
                $days =  ($int > 0) ? $int.'天过期':"已过期";
                return $this->codes->code."(".$days.")";

            }else{
                return "无激活码";
            }
        };
        $fields['created_at'] = function() {

            return date('Y-m-d',$this->created_at);
        };

        $fields['pic_url'] = function () {
            $size = Yii::$app->params['oss']['Size']['320x320'];
            if($this->image){
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
       $fields['user_role'] = function() {
            return "student";
       };
       $fields['identifier'] = function () {
             return 'student'.$this->id;
       };
        unset(
            $fields['id'],
            $fields['student_id'],
            $fields['campus_id'],
            $fields['studio_id'],
            $fields['years'],
            $fields['password_hash'],
            $fields['national_id'],
            $fields['family_member_name'],
            $fields['relationship'],
            $fields['organization'],
            $fields['position'],
            $fields['contact_phone'],
            $fields['race'],
            $fields['student_type'],
            $fields['image'],
            $fields['is_image'],
            $fields['career_pursuit_type'],
            $fields['residence_type'],
            $fields['city'],
            $fields['detailed_address'],
            $fields['qq_number'],
            $fields['united_exam_province'],
            $fields['fine_art_instructor'],
            $fields['exam_participant_number'],
            $fields['sketch_score'],
            $fields['color_score'],
            $fields['quick_sketch_score'],
            $fields['design_score'],
            $fields['verbal_score'],
            $fields['math_score'],
            $fields['english_score'],
            $fields['pre_school_assessment'],
            $fields['is_graduation'],
            $fields['note'],
            $fields['is_all_visible'],
            $fields['auth_key'],
            $fields['password_reset_token'],
            $fields['device_token'],
            $fields['token_value'],
            $fields['updated_at'],
            $fields['is_review'],
           # $fields['usersig'],
            $fields['status'],
            $fields['access_token'],
            $fields['gender']
        );


		return $fields;
	}

    //获取过期时间
    public function getExpire($time,$year) {
        if($year >= 1){
            $year = floor($year);
            return floor((strtotime("+$year years" ,$time)-time())/24/3600);
        }else{
            $month =  floor($year*12);
            return floor((strtotime("+$month months",$time)-time())/24/3600);
        }
    }


    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->where(['type'=>2])->alias('codes');
    }

}

?>