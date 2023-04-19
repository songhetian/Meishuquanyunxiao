<?php 
	
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use teacher\modules\v2\models\UserHomeWork;
use teacher\modules\v2\models\Admin;
use components\Spark;

class User extends \common\models\User
{
    public function beforeSave($insert)
    {
        //新数据插入
        if ($this->isNewRecord) {
            
            $this->created_at = time();
            $this->updated_at = time();
            $this->generateAuthKey();
            $this->setUuid();
        }else{
            // $usersig = Tencat::CreateNumber('student'.$this->id);
            // $this->usersig = current($usersig);
        }
        return true;
    }
	public function fields()
	{
	    $fields = parent::fields();
	    $fields['user_id'] = function() {
	    	return $this->id;
	    };
        
	    unset(
	    	$fields['id']
	    );
	   	$fields['homeworks'] = function (){
	   		$array = $this->homeworks;
	   		foreach ($array as $key => $value) {
	   			if(!empty($value['image'])){
	   				$image = $value['image'];
		    		$size = Yii::$app->params['oss']['Size']['350x350'];
		    		$studio = Campus::findOne(User::findOne($this->id)->campus_id)->studio_id;
		            $array[$key]['image'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            $size = Yii::$app->params['oss']['Size']['original'];
		            $array[$key]['image_original'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            	$array[$key]['evaluator']  = Admin::findOne($value['evaluator'])['name'];
		            $array[$key]['created_at'] = date("Y/m/d",$value['created_at']);
		            $array[$key]['user_name']  = $this->name;
	   			}
	   			if(!empty($value['comment_image'])){
	   				$comment_image = $value['comment_image'];
		    		$size = Yii::$app->params['oss']['Size']['350x350'];
		    		$studio = Campus::findOne(User::findOne($this->id)->campus_id)->studio_id;
		            $array[$key]['comment_image'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            $size = Yii::$app->params['oss']['Size']['original'];
		            $array[$key]['comment_image_original'] = Oss::getUrl($studio, 'user-homework', 'image', $image).$size;
		            	$array[$key]['evaluator']  = Admin::findOne($value['evaluator'])['name'];
	   			}
	   			if(!empty($value['video'])){
	   				$array[$key]['video']  = (Object)[
	                    'cc_id' => $value['video'],
	                    'charging_option' => $value['charging_option'],
	                    'duration' =>  Spark::getDuration($value['video'], $value['charging_option']*10),
                	];
	   			}
	   			if(!empty($value['course_id'])) {
	   				$array[$key]['course_id'] = $value['course_id'];
	   			}
	   		}

	   		return $array;
	    };

	    return $fields;
	}

    public function rules()
    {
        return [
            //普通注册
            [['campus_id'], 'required', 'on' => ['create', 'update', 'alidayu']],

            [['name'],'UniqueName','on'=>['modify','perfect']],

            [['phone_number'],'UniquePhone','on'=>['modify','perfect']],

            [['name'],'required','on' => 'perfect'],
            //手机验证码
            ['phone_verify_code','required','on' => ['alidayu']],
            
            ['phone_verify_code','phoneVerifyCode','on' => ['alidayu']],
            //字段规范
            [['national_id', 'exam_participant_number', 'qq_number','password_reset_token','access_token'], 'unique'],
            ['phone_number', 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9]|19[0-9])[0-9]{8}$/', 'message' => '请填写有效手机号!'],
             [['name'], 'match', 'pattern' => '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]+$/u', 'message'=>'姓名中不能有符号。','on'=>['modify','perfect']],
            ['auth_key', 'default', 'value' => self::AUTH_KEY], 
            ['is_graduation', 'default', 'value' => self::GRADUAT_NOT_YET],
            ['is_all_visible', 'default', 'value' => self::PARTIAL_VISIBLE],
            ['is_review', 'default', 'value' => self::REVIEW_NOT_YET],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['is_first', 'default', 'value' => 0], 

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
            [['student_id', 'campus_id', 'class_id', 'gender', 'relationship', 'race', 'student_type', 'career_pursuit_type', 'residence_type', 'grade', 'province', 'city', 'united_exam_province', 'is_graduation', 'graduation_at', 'is_all_visible', 'admin_id', 'created_at', 'updated_at', 'is_review', 'status','years','studio_id','is_image','is_first'], 'integer'],
            [['sketch_score', 'color_score', 'quick_sketch_score', 'design_score', 'verbal_score', 'math_score', 'english_score', 'total_score'], 'number'],
            [['pre_school_assessment', 'note'], 'string'],
            [['name', 'family_member_name', 'organization', 'position', 'school_name', 'fine_art_instructor', 'auth_key', 'access_token','qrcode'], 'string', 'max' => 32],
            [['national_id'], 'string', 'max' => 18],
            [['contact_phone', 'qq_number', 'phone_number'], 'string', 'max' => 11],
            [['detailed_address'], 'string', 'max' => 50],
            [['exam_participant_number'], 'string', 'max' => 14],
            [['image'], 'string', 'max' => 100],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 100],
            [['device_token'], 'string', 'max' => 100],
            [['usersig'], 'string', 'max' => 500],
            [['username'],'safe'],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
                'maxFiles' => 100

            ],
        ];
    }

    public function getRealNameAuthParams(){
        $params = array(
          'idcard' => $this->national_id,//身份证号码
          'realname' => $this->name,//真实姓名
          'key' => 'a4d5a4307d57a0c2538f8302386f8f89',//应用APPKEY(应用详细页查询)
        );
        return http_build_query($params);
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

    //身份证实名认证
    public function realNameAuth($attribute){
        $url = 'http://op.juhe.cn/idcard/query';
        $params = $this->getRealNameAuthParams();
        $ispost = 0;

        $httpInfo = array();
        $ch = curl_init();
     
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        $result = json_decode($response,true);
        if($result){
            if($result['error_code']=='0'){
                if($result['result']['res'] == '1'){
                    return;
                }else{
                    $this->addError('national_id', '身份证号码和真实姓名不匹配');
                }
                #print_r($result);
            }else{
                $this->addError('national_id', '您的身份证号码无效，请检查');
                //echo $result['error_code'].":".$result['reason'];
            }
        }else{
            $this->addError('national_id', '联网验证身份证失败,请联系管理员');
            //echo "请求失败";
        }
    }
	//获取班级学生id集合
	public static function getUsers($class_id) {

		$users = self::find()->select('id,name')->where(['class_id'=>$class_id,'status'=>self::STATUS_ACTIVE])->asArray()->all();

		return array_column($users,'id');
	}

	/**
	 * [多条获取作业]
	 *
	 *
	 *
	*/
	public function getHomeworks()
	{
		return $this->hasMany(UserHomeWork::className(),['user_id'=>'id'])->select(['homework_id'=>'id','image','comments','score','evaluator','created_at','course_material_id','video','comment_image','charging_option','course_id'])->orderby('video DESC,score DESC')->asArray()->alias('homeworks');
	}


}

?>