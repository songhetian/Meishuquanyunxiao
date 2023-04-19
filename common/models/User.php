<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;
use backend\models\Admin;
use backend\models\ActiveRecord;
use common\models\City;
use common\models\Campus;
use common\models\UserClass;
use common\models\Classes;
use common\models\Race;
use common\models\ActivationCode;
use common\models\Format;
use components\Alidayu;
use components\Jpush;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property integer $student_id
 * @property integer $campus_id
 * @property integer $class_id
 * @property string $name
 * @property string $phone_number
 * @property string $password_hash
 * @property integer $gender
 * @property string $national_id
 * @property string $family_member_name
 * @property integer $relationship
 * @property string $organization
 * @property string $position
 * @property string $contact_phone
 * @property integer $race
 * @property integer $student_type
 * @property integer $career_pursuit_type
 * @property integer $residence_type
 * @property integer $grade
 * @property integer $province
 * @property integer $city
 * @property string $detailed_address
 * @property string $qq_number
 * @property string $school_name
 * @property integer $united_exam_province
 * @property string $fine_art_instructor
 * @property string $exam_participant_number
 * @property double $sketch_score
 * @property double $color_score
 * @property double $quick_sketch_score
 * @property double $design_score
 * @property double $verbal_score
 * @property double $math_score
 * @property double $english_score
 * @property double $total_score
 * @property string $pre_school_assessment
 * @property integer $credit
 * @property integer $is_graduation
 * @property integer $graduation_at
 * @property integer $is_all_visible
 * @property string $note
 * @property string $auth_key
 * @property string $password_reset_token
 * @property string $device_token
 * @property string $access_token
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $is_review
 * @property integer $status
 */
class User extends ActiveRecord implements IdentityInterface
{
    const GENDER_MALE = 10;
    const GENDER_FEMALE = 20;

    const RELATIONSHIP_MOTHER = 10;
    const RELATIONSHIP_FATHER = 20;
    const RELATIONSHIP_KIN = 30;

    const STUDENT_CURRENT = 10;
    const STUDENT_ALUMNI = 20;

    const CAREER_PURSUIT_LIBERAL_ARTS = 10;
    const CAREER_PURSUIT_SCIENCE = 20;

    const RESIDENCE_CITY = 10;
    const RESIDENCE_TOWN = 20;

    const GRADE_FRESHMEN = 10;
    const GRADE_JUNIOR = 20;
    const GRADE_SENIOR = 30;

    const GRADUAT_NOT_YET = 0;
    const GRADUAT_ED = 10;

    const PARTIAL_VISIBLE = 0;
    const ALL_VISIBLE = 10;

    const REVIEW_NOT_YET = 0;
    const REVIEW_ED = 10;

    const AUTH_KEY = '134679';
    public $avatar;
    public $username;
    public $phone_verify_code;
    public $old_class_id;
    public $old_is_review;
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            //新数据插入
            if ($this->isNewRecord) {
                $this->student_id = $this->generateStudioId();
                if ($this->password_hash) {
                    $this->setPassword($this->password_hash);
                }
                $this->generateAuthKey();
                $this->setUuid();
                $admin_id = Yii::$app->user->identity->id;
                if($admin_id){
                    $this->admin_id = $admin_id;
                }
            }else{
                //数据更新
                if ($this->password_hash != $this->getOldAttribute('password_hash')) {
                    $this->setPassword($this->password_hash);
                }  
            }
            $this->old_class_id = $this->getOldAttribute('class_id');
            $this->old_is_review = $this->getOldAttribute('is_review');
            return true;
        }
        return false;
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //班级处理
        if($this->class_id && $this->class_id != $this->old_class_id){
            $model = new UserClass();
            $model->user_id = $this->id;
            $model->class_id = $this->class_id;
            $model->save();
        }
        
        //审核处理
        // if ($this->old_is_review == self::REVIEW_NOT_YET && $this->is_review == self::REVIEW_ED && $this->device_token != NULL){
        //     $client = new Jpush();
        //     $client->sendPrivateNotification($this->device_token, '您的账号已审核通过', ['code' => 1000]);
        //     $this->device_token = NULL;
        //     $this->save();
        // }
        
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //普通注册
            [['campus_id'], 'required', 'on' => ['create', 'update', 'alidayu']],
            [['gender', 'national_id', 'family_member_name', 'relationship', 'organization', 'position', 'contact_phone'], 'required', 'on' => ['api_update']],
            //手机验证码
            ['phone_verify_code','required','on' => ['alidayu']],
            #['phone_verify_code','phoneVerifyCode','on' => ['alidayu']],
            //字段规范
            [['national_id', 'exam_participant_number', 'qq_number', 'phone_number', 'password_reset_token','access_token'], 'unique'],
            [['national_id'], 'match', 'pattern' => '/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/', 'message'=>'请填写有效身份证。'],
            [['phone_number', 'contact_phone'], 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|19[0-9])[0-9]{8}$/', 'message'=>'请填写有效手机号。'],
            
            [['name'],'UniqueName','on'=>['modify','perfect']],

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
            ['is_first', 'default', 'value' => 0], 
            [['national_id'], 'realNameAuth', 'on' => ['student_info']],

            [['password_hash'],'string', 'min' => 6],
            //字段类型
            [['student_id', 'campus_id', 'class_id', 'gender', 'relationship', 'race', 'student_type', 'career_pursuit_type', 'residence_type', 'grade', 'province', 'city', 'united_exam_province', 'credit', 'is_graduation', 'graduation_at', 'is_all_visible', 'admin_id', 'created_at', 'updated_at', 'is_review', 'years','status','studio_id','is_image','is_first'], 'integer'],
            [['sketch_score', 'color_score', 'quick_sketch_score', 'design_score', 'verbal_score', 'math_score', 'english_score', 'total_score'], 'number'],
            [['pre_school_assessment', 'note'], 'string'],
            [['name', 'family_member_name', 'organization', 'position', 'school_name', 'fine_art_instructor', 'auth_key', 'access_token','qrcode'], 'string', 'max' => 32],
            
            [['national_id'], 'string', 'max' => 18],
            [['contact_phone', 'qq_number', 'phone_number'], 'string', 'max' => 11],
            [['detailed_address'], 'string', 'max' => 50],
            [['image'], 'string', 'max' => 50],
            [['exam_participant_number'], 'string', 'max' => 14],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 100],
            [['device_token'], 'string', 'max' => 100],
            [['username'],'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'student_id' => Yii::t('app', '学号'),
            'campus_id' => Yii::t('app', '所属校区'),
            'class_id' => Yii::t('app', '所在班级'),
            'name' => Yii::t('app', '真实姓名'),
            'phone_number' => Yii::t('app', '手机号'),
            'phone_verify_code' => Yii::t('app', '验证码'),
            'password_hash' => Yii::t('app', '密码'),
            'gender' => Yii::t('app', '性别'),
            'national_id' => Yii::t('app', '身份证号码'),
            'family_member_name' => Yii::t('app', '家庭成员姓名'),
            'relationship' => Yii::t('app', '与本人关系'),
            'organization' => Yii::t('app', '单位'),
            'position' => Yii::t('app', '职务'),
            'contact_phone' => Yii::t('app', '联系电话'),
            'race' => Yii::t('app', '民族'),
            'student_type' => Yii::t('app', '考生类别'),
            'image' => Yii::t('app', '头像'),
            'is_image' => Yii::t('app', '是否掉用student目录'),
            'career_pursuit_type' => Yii::t('app', '文理科'),
            'residence_type' => Yii::t('app', '户口类别'),
            'grade' => Yii::t('app', '年级'),
            'province' => Yii::t('app', '省'),
            'city' => Yii::t('app', '市'),
            'detailed_address' => Yii::t('app', '详细住址'),
            'qq_number' => Yii::t('app', 'QQ号码'),
            'school_name' => Yii::t('app', '就读学校'),
            'united_exam_province' => Yii::t('app', '联考省份'),
            'fine_art_instructor' => Yii::t('app', '高中美术老师'),
            'exam_participant_number' => Yii::t('app', '考生号'),
            'sketch_score' => Yii::t('app', '素描'),
            'color_score' => Yii::t('app', '色彩'),
            'quick_sketch_score' => Yii::t('app', '速写'),
            'design_score' => Yii::t('app', '设计'),
            'verbal_score' => Yii::t('app', '语文'),
            'math_score' => Yii::t('app', '数学'),
            'english_score' => Yii::t('app', '英语'),
            'total_score' => Yii::t('app', '综合'),
            'pre_school_assessment' => Yii::t('app', '入学测试评估'),
            'credit' => Yii::t('app', '学分'),
            'is_graduation' => Yii::t('app', '是否毕业'),
            'graduation_at' => Yii::t('app', '毕业时间'),
            'is_all_visible' => Yii::t('app', '是否全部可见'),
            'note' => Yii::t('app', '备注'),
            'auth_key' => Yii::t('app', '认证密钥'),
            'password_reset_token' => Yii::t('app', '密码重置Token'),
            'device_token' => Yii::t('app', '设备令牌'),
            'access_token' => Yii::t('app', '访问令牌'),
            'admin_id' => Yii::t('app', '创建者'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'is_review' => Yii::t('app', '是否审核'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * 根据ID查询用户
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的用户对象
     */
    public static function findIdentity($id)
    {
        return static::findOne([
            'id' => $id,
            'is_graduation' => self::GRADUAT_NOT_YET, 
            'is_review' => self::REVIEW_ED, 
            'status' => self::STATUS_ACTIVE
        ]);
    }

    /**
     * 根据 token 查询用户 RESTFUL认证使用
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的用户对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }


    public function getRealNameAuthParams(){
        $params = array(
          'idcard' => $this->national_id,//身份证号码
          'realname' => $this->name,//真实姓名
          'key' => 'a4d5a4307d57a0c2538f8302386f8f89',//应用APPKEY(应用详细页查询)
        );
        return http_build_query($params);
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
    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * 验证登录密钥
     * 
     * @param string $authKey
     * @return boolean 如果当前用户身份验证的密钥是有效的
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function signup()
    {
        if ($this->validate()) {
            if($this->save()){
                return $this;
            }
        }
        return false;
    }

    public function generateStudioId()
    {
        $max = static::find()->max('student_id');

        $number = date('y') * 10000 + 1;

        $max = ($max) ? $number + substr($max, 2, 4) : $number;

        return $max;
    }

    /**
     * 根据手机号查询用户
     *
     * @param string $phone_number
     * @return static|null
     */
    public static function findByPhoneNumber($phone_number, $studio_id = NULL)
    {
        return static::find()->andFilterWhere([
            'phone_number' => $phone_number, 
            'is_graduation' => self::GRADUAT_NOT_YET, 
            'status' => self::STATUS_ACTIVE,
            'campus_id' => ($studio_id) ? Campus::getCampuses($studio_id) : [],
        ])->one();
    }

    /**
     * 根据学号查询用户
     *
     * @param string $student_id
     * @return static|null
     */
    public static function findByStudentId($student_id,$campus_id)
    {
        $campus_list = Campus::getCampuses($studio_id);
        return static::findOne([
            'student_id' => $student_id,
            'is_graduation' => self::GRADUAT_NOT_YET,
            'status' => self::STATUS_ACTIVE,
            'campus_id' => $campus_list,
        ]);
    }

    /**
     * 生成access_token RESTFUL认证使用
     */
    public function generateAccessToken()  
    {  
        $this->access_token = Yii::$app->security->generateRandomString();  
    }

    /**
     * 密码验证
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * 生成并设置密码
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 生成 记住我 的认证密钥
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'gender' => [
                self::GENDER_MALE => Yii::t('common', 'Male'),
                self::GENDER_FEMALE => Yii::t('common', 'Female'),
            ],
            'relationship' => [
                self::RELATIONSHIP_MOTHER => Yii::t('common', 'Mother'),
                self::RELATIONSHIP_FATHER => Yii::t('common', 'Father'),
                self::RELATIONSHIP_KIN => Yii::t('common', 'Kin'),
            ],
            'student_type' => [
                self::STUDENT_CURRENT => Yii::t('common', 'Current'),
                self::STUDENT_ALUMNI => Yii::t('common', 'Alumni'),
            ],
            'career_pursuit_type' => [
                self::CAREER_PURSUIT_LIBERAL_ARTS => Yii::t('common', 'Liberal Arts'),
                self::CAREER_PURSUIT_SCIENCE => Yii::t('common', 'Science'),
            ],
            'residence_type' => [
                self::RESIDENCE_CITY => Yii::t('common', 'City'),
                self::RESIDENCE_TOWN => Yii::t('common', 'Town'),
            ],
            'grade' => [
                self::GRADE_FRESHMEN => Yii::t('common', 'Freshmen'),
                self::GRADE_JUNIOR => Yii::t('common', 'Junior'),
                self::GRADE_SENIOR => Yii::t('common', 'Senior'),
            ],
            'is_graduation' => [
                self::GRADUAT_NOT_YET => Yii::t('common', 'Not Graduated'),
                self::GRADUAT_ED => Yii::t('common', 'Graduated'),
            ],
            'is_all_visible' => [
                self::PARTIAL_VISIBLE => Yii::t('common', 'Partial Visible'),
                self::ALL_VISIBLE => Yii::t('common', 'All Visible'),
            ],
            'is_review' => [
                self::REVIEW_NOT_YET => Yii::t('common', 'Not Reviewed'),
                self::REVIEW_ED => Yii::t('common', 'Reviewed'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE =>  Yii::t('backend',  'Not Deleted'),                
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    //手机验证码验证
    public function phoneVerifyCode($attribute)
    {
        if($this->phone_number && $this->phone_verify_code) {
            $alidayu = new Alidayu();
            $res = $alidayu->phoneVerifyCode($this);
            if($res == false){
                $this->addError('phone_verify_code', '无效的验证码');
            }
        }
    }
    
    public function isAllVisible()
    {
        $this->is_all_visible = ($this->is_all_visible) ? self::PARTIAL_VISIBLE : self::ALL_VISIBLE;
        if($this->save()){
            return true;
        }
        return false;
    }

    public function isReview()
    {
        $this->is_review = ($this->is_review) ? self::REVIEW_NOT_YET : self::REVIEW_ED;
        if($this->save()){
            return true;
        }
        return false;
    }

    public function updateStatus()
    {
        $this->status = self::STATUS_DELETED;
        $this->is_review = self::REVIEW_NOT_YET;
        $this->save(false);
        return true;
    }

    public static function getUserList()
    {
        $model = static::findAll([
            'is_graduation' => self::GRADUAT_NOT_YET,
            'is_review' => self::REVIEW_ED,
            'status' => self::STATUS_ACTIVE
        ]);
        return ArrayHelper::map($model, 'id', 'name');
    }

    public static function isNumberOfReviewFull()
    {
        $campus_id = Campus::getCampuses(Format::getStudio('id'));
        $num = static::find()->where(['campus_id' => $campus_id, 'is_review' => self::REVIEW_ED])->count();
        return ($num < Format::getStudio('review_num')) ? true : false;
    }

    public function getCampuses()
    {
        return $this->hasOne(Campus::className(), ['id' => 'campus_id'])->alias('campuses');
    }

    public function getClasses()
    {
        return $this->hasOne(Classes::className(), ['id' => 'class_id'])->alias('classes');
    }

    public function getRaces()
    {
        return $this->hasOne(Race::className(), ['id' => 'race'])->alias('races');
    }

    public function getProvinces()
    {
        return $this->hasOne(City::className(), ['id' => 'province'])->alias('provinces');
    }

    public function getCitys()
    {
        return $this->hasOne(City::className(), ['id' => 'city'])->alias('citys');
    }
    
    public function getUnitedExamProvinces()
    {
        return $this->hasOne(City::className(), ['id' => 'united_exam_province'])->alias('united_exam_provinces');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }

    public function getCodes()
    {
        return $this->hasOne(ActivationCode::className(), ['relation_id'=>'id'])->where(['codes.type' => 2])->alias('codes');
    }

    public function setUuid()
    {
        $chars = md5(uniqid(mt_rand(), true));  
        $uuid  = substr($chars, 0, 8) . '-';  
        $uuid .= substr($chars, 8, 4) . '-';  
        $uuid .= substr($chars, 12, 4) . '-';  
        $uuid .= substr($chars, 16, 4) . '-';  
        $uuid .= substr($chars, 20, 12);
        $this->token_value = md5($uuid);
    }
}