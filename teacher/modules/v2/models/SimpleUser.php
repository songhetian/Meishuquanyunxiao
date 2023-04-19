<?php 
	
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;

class SimpleUser extends \common\models\User
{

    public $studio_name;
    
	public function fields()
	{
		$fields = parent::fields();

		$fields['identifier'] = function () {

			return 'student'.$this->admin_id;
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

        $fields['campus_id'] = function () {
        	return Campus::findOne($this->campus_id)->name;
        };
        $fields['gender'] = function () {
        	return self::getValues('gender',$this->gender);
        };
        $fields['class_id'] = function () {
        	return Classes::findOne($this->class_id)->name;
        };
        $fields['national_id'] = function () {
        	return $this->national_id ? '已认证' : '未认证';
        };
        if($this->province){
            $fields['province'] = function() {
            	return City::findOne($this->province)->name;
            };
        }
        $fields['grade'] = function() { 
        	return self::getValues('grade',$this->grade);
        };
        $fields['phone_number'] = function() {
            return $this->phone_number ? $this->phone_number : '未设置';
        };
       //  $fields['school_name'] = function() {
       //  	return $this->school_name ? $this->school_name : '未设置';
       //  };
       //  $fields['graduation_at'] = function() {
       //  	return $this->graduation_at ? $this->graduation_at : '未设置';
       //  };
      	// $fields['total_score'] = function() {
       //  	return $this->total_score ? $this->total_score : '未设置';
       //  };
        $fields['code_number'] = function () {
            if($this->codes1) {
                if(strtotime($this->codes1->due_time) >= time()) {
                    return $this->codes1->code;
                }else{
                    return '';
                }  
            }
        };
        $fields['campusId_Rn'] = function () {
            return $this->campus_id;
        };
		$fields['user_role'] = function () {

			return "student";
		};
        $fields['is_create'] = function () {

            return false;
        };

        $fields['studio_name'] = function () {
            return Studio::findOne($this->studio_id)->name; 
        };  
        $fields['is_vip'] = function () {
            if(!empty($this->vip_time) && strtotime($this->vip_time) > time()){
                return true;
            }else{
                return false;
            }
            
        };  

        $fields['vip_time'] = function() {
            if($this->codes1){
              return  $this->codes1->due_time;
            }else{
              return  $this->vip_time;
            }
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
        };
        
		return $fields;
	}
   public function rules()
    {
        return [
            //普通注册
            [['campus_id'], 'required', 'on' => ['create', 'update', 'alidayu']],
            [['gender', 'national_id', 'family_member_name', 'relationship', 'organization', 'position', 'contact_phone'], 'required', 'on' => ['api_update']],

            [['name','national_id','grade'],'required','on' => 'perfect'],

            [['name'],'UniqueName','on'=>['modify','perfect']],

            [['phone_number'],'UniquePhone','on'=>'modify'],
            //手机验证码
            ['phone_verify_code','required','on' => ['alidayu']],
            ['phone_verify_code','phoneVerifyCode','on' => ['alidayu']],
            //字段规范
            [['national_id', 'exam_participant_number', 'qq_number', 'password_reset_token','access_token'], 'unique'],
            [['national_id'], 'match', 'pattern' => '/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/', 'message'=>'请填写有效身份证。'],
            ['phone_number', 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9]|19[0-9])[0-9]{8}$/', 'message' => '请填写有效手机号!'],
            
            [['name'], 'match', 'pattern' => '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]+$/u', 'message'=>'姓名中不能有符号。','on'=>['modify','perfect']],
            ['auth_key', 'default', 'value' => self::AUTH_KEY], 
            ['is_graduation', 'default', 'value' => self::GRADUAT_NOT_YET],
            ['is_all_visible', 'default', 'value' => self::PARTIAL_VISIBLE],
            ['is_review', 'default', 'value' => self::REVIEW_NOT_YET],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 

            ['gender', 'in', 'range' => [self::GENDER_MALE, self::GENDER_FEMALE]],
            ['relationship', 'in', 'range' => [self::RELATIONSHIP_MOTHER, self::RELATIONSHIP_FATHER, self::RELATIONSHIP_KIN]],
            ['student_type', 'in', 'range' => [self::STUDENT_CURRENT, self::STUDENT_ALUMNI]],
            ['career_pursuit_type', 'in', 'range' => [self::CAREER_PURSUIT_LIBERAL_ARTS, self::CAREER_PURSUIT_SCIENCE]],
            ['residence_type', 'in', 'range' => [self::RESIDENCE_CITY, self::RESIDENCE_TOWN]],
            ['grade', 'in', 'range' => [self::GRADE_FRESHMEN, self::GRADE_JUNIOR, self::GRADE_SENIOR]],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['years', 'in', 'range' => [1,2,3]],
            [['national_id'], 'realNameAuth', 'on' => ['student_info']],
            [['password_hash'],'string', 'min' => 6],
            //字段类型
            [['student_id', 'campus_id', 'class_id', 'gender', 'relationship', 'race', 'student_type', 'career_pursuit_type', 'residence_type', 'grade', 'province', 'city', 'united_exam_province', 'is_graduation', 'graduation_at', 'is_all_visible', 'admin_id', 'created_at', 'updated_at', 'is_review', 'status','years'], 'integer'],
            [['sketch_score', 'color_score', 'quick_sketch_score', 'design_score', 'verbal_score', 'math_score', 'english_score', 'total_score'], 'number'],
            [['pre_school_assessment', 'note'], 'string'],
            [['name', 'family_member_name', 'organization', 'position', 'school_name', 'fine_art_instructor', 'auth_key', 'access_token'], 'string', 'max' => 32],
            [['national_id'], 'string', 'max' => 18],
            [['contact_phone', 'qq_number', 'phone_number'], 'string', 'max' => 11],
            [['detailed_address'], 'string', 'max' => 50],
            [['exam_participant_number'], 'string', 'max' => 14],
            [['image'], 'string', 'max' => 100],
            [['password_hash', 'password_reset_token','vip_time'], 'string', 'max' => 100],
            [['device_token'], 'string', 'max' => 100],
            [['usersig','token_value'], 'string', 'max' => 500],
            [['username'],'safe'],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
                'maxFiles' => 100

            ],
        ];
    }

    //手机验证码验证
    public function UniquePhone($attribute)
    {
        $list =  self::find()
                     ->select('phone_number')
                     ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$this->studio_id])
                     ->andWhere(['<>','id',$this->id])
                     ->asArray()
                     ->all();
        $phone_number = array_column($list, 'phone_number');  

        if(in_array($this->phone_number, $phone_number)) {
             $this->addError('phone', "{$this->phone_number}已经被占用");
        }else{
            return true;
        }         
    }

    //名字不能重复
    public function UniqueName($attribute)
    {
        $list =  self::find()
                     ->select('name')
                     ->where(['status'=>self::STATUS_ACTIVE,'studio_id'=>$this->studio_id])
                     ->andWhere(['<>','id',$this->id])
                     ->asArray()
                     ->all();
        $name = array_column($list, 'name');  

        if(in_array($this->name, $name)) {
             $this->addError('name', "该昵称已经被占用");
        }else{
            return true;
        }         
    }

    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->where(['type'=>2])->alias('codes');
    }
    public function getCodes2()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'id'])->alias('codes2');
    }
    public function getCodes1()
    {
        return $this->hasOne(ActivationCode::className(),['relation_id'=>'admin_id'])->where(['type'=>2])->alias('codes1');
    }
}

?>