<?php

namespace teacher\modules\v2\models;

use Yii;
use backend\models\ActiveRecord;
use common\models\Category;
#use backend\models\Admin;
use common\models\InstructionMethod;
use common\models\Group;
use common\models\Picture;
use common\models\Video;
use components\Oss;
use components\Spark;
/**
 * This is the model class for table "{{%course}}".
 *
 * @property integer $id
 * @property integer $class_period_id
 * @property integer $class_id
 * @property integer $category_id
 * @property integer $instructor
 * @property integer $instruction_method_id
 * @property integer $course_material_id
 * @property integer $started_at
 * @property integer $ended_at
 * @property string $class_content
 * @property string $class_emphasis
 * @property string $note
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Course extends ActiveRecord
{
    const HANDLE_COPY = 1;
    const HANDLE_CUT  = 2;

    static $is_view = 1;
    static $user_id = 0;
    
    public static function tableName()
    {
        return '{{%course}}';
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['course_id'] = function() {
            return $this->id;
        };

        if($this->classPeriods){
            $fields['class_period_id'] = function () {
                return [
                    'class_period_id' => $this->classPeriods->id,
                    'name' => $this->classPeriods->name,
                    'started_at' => $this->classPeriods->started_at,
                    'dismissed_at' => $this->classPeriods->dismissed_at,
                    'position' => $this->classPeriods->position,
                ];
            };
        }

        if($this->classes){
            $fields['class'] = function () {
                return [
                    'class_id' => $this->classes->id,
                    'name' => $this->classes->name
                ];
            };
        }

        if($this->categorys){
            $fields['category_id'] = function () {
                return [
                    'category_id' => $this->categorys->id,
                    'name' => $this->categorys->name,
                    'color' => $this->categorys->color
                ];
            };
        }else{
            $fields['category_id'] = function () {
                return [
                    'category_id' => 0,
                    'name' => "未设置",
                    'color' => "#ffc700"
                ];
            };     
        }

        if($this->instructors){
            $fields['instructor'] = function () {

                $TeacherList =  NewClasses::getCourseAdminList($this->course_material_id);

                if(in_array(self::$user_id, $TeacherList)) {
                    $admin_id = self::$user_id;
                }else{
                    $admin_id = $this->courseMaterials->admin_id;
                }  
                return [
                    'instructor_id' => $admin_id,
                    'name' => $this->instructors->name
                ];
            };
        }

        if($this->instructionMethods){
            $fields['instruction_method_id'] = function () {
                return [
                    'instruction_method_id' => $this->instructionMethods->id,
                    'name' => $this->instructionMethods->name
                ];
            };
        }else{
            $fields['instruction_method_id'] = function () {
                return [
                    'instruction_method_id' => 0,
                    'name' => '未设置'
                ];
            };
        }

        if($this->courseMaterials){

            if(self::$is_view == 1){
                $fields['course_material_id'] = function () {

                    $TeacherList =  NewClasses::getCourseAdminList($this->course_material_id);

                    if(in_array(self::$user_id, $TeacherList)) {
                        $admin_id = self::$user_id;
                    }else{
                        $admin_id = $this->courseMaterials->admin_id;
                    }

                    $host_info = Yii::$app->request->hostInfo.'/assets';
                    
                    $description = preg_replace('/api.teacher.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->courseMaterials->description));
                    $description = preg_replace('/(http|https):\/\//', 'http://', $description);
                    return [
                        'course_material_id' => $this->courseMaterials->id,
                        'admin_id'           => $admin_id,
                        'name' => $this->courseMaterials->name,
                        'depict' => stripslashes($description),
                        'picture' => $this->getMaterials(Picture::className(), 'picture', 'image', Group::TYPE_PICTURE),
                        'video' => $this->getMaterials(Video::className(), 'video', 'preview', Group::TYPE_VIDEO),
                    ];
                };
            }elseif(self::$is_view == 2){
                $fields['course_material_id'] = function () {

                    $TeacherList =  NewClasses::getCourseAdminList($this->course_material_id);

                    if(in_array(self::$user_id, $TeacherList)) {
                        $admin_id = self::$user_id;
                    }else{
                        $admin_id = $this->courseMaterials->admin_id;
                    }

                    $host_info = Yii::$app->request->hostInfo.'/assets';
                    
                    $description = preg_replace('/api.teacher.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->courseMaterials->description));
                    $description = preg_replace('/(http|https):\/\//', 'http://', $description);
                    return [
                        'course_material_id' => $this->courseMaterials->id,
                        'admin_id'           => $admin_id,
                        'name' => $this->courseMaterials->name,
                        'depict' => stripslashes($description),
                        'pic_number' => $this->getCount(Group::TYPE_PICTURE),
                        'vid_number'   => $this->getCount(Group::TYPE_VIDEO) ,
                    ];
                };
            }
        }
        
        $fields['started_at'] = function () {
            return date("Y/m/d", $this->started_at);
        };

        $fields['class_content'] = function() {

            return  $this->courseMaterials->name;
        };

        $fields['ended_at'] = function () {
            return date("Y/m/d", $this->ended_at);
        };

        if($this->prew_image) {
            $fields['prew_image'] = function () {
                $size = Yii::$app->params['oss']['Size']['320x320'];
                $image =  Picture::findOne($this->prew_image);
                $studio = Admin::findOne($image->admin_id)->studio_id;
                $image = ($image->source == Picture::SOURCE_LOCAL) ? Oss::getUrl($studio, 'picture', 'image', $image->image) : $image->image;
                return $image.$size;
            };
        }else{
            $fields['prew_image'] = function () {
               return CourseMaterial::getPreviewById($this->courseMaterials->id); 
            };
        }
        $fields['attendance'] = function() {

            $total = User::find()->where(['class_id'=>$this->class_id,'status'=>10])->count();
            return '('.Sign::GetNum($this->id,$this->class_period_id).'/'.$total.')';
        }; 
        unset(
            $fields['id'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
        return $fields;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->started_at = (!is_int($this->started_at)) ? strtotime($this->started_at) : $this->started_at;
            $this->ended_at = (!is_int($this->ended_at)) ? strtotime($this->ended_at) : $this->ended_at;
            $this->updated_at = time();
            $this->created_at = time();
            if ($this->isNewRecord) {
            }else{
                $this->updated_at = time();
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['class_period_id', 'class_id', 'instructor', 'started_at', 'ended_at'], 'required'],

            [['class_content','instruction_method_id','category_id'], 'required','on'=>['create']],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'course_material_id', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['class_content', 'class_emphasis', 'note','prew_image'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'class_period_id' => Yii::t('app', '上课时间'),
            'class_id' => Yii::t('app', '所属班级'),
            'category_id' => Yii::t('app', '科目'),
            'instructor' => Yii::t('app', '教学老师'),
            'instruction_method_id' => Yii::t('app', '教学形式'),
            'course_material_id' => Yii::t('app', '选择课件'),
            'started_at' => Yii::t('app', '开始时间'),
            'ended_at' => Yii::t('app', '结束时间'),
            'class_content' => Yii::t('app', '教学内容'),
            'class_emphasis' => Yii::t('app', '教学重点'),
            'note' => Yii::t('app', '备注'),
            'admin_id' => Yii::t('app', '上传者'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    static public function getDisabledDates($class_period_id, $class_id, $id = 0)
    {
        $res = [];
        if($class_period_id && $class_id){
            $query =  static::find()->andFilterWhere(['class_period_id' => $class_period_id, 'class_id' => $class_id , 'status' => self::STATUS_ACTIVE]);
            if($id){
                $query->andFilterWhere(['!=', 'id', $id]);
            }
            $model = $query->all();
            if($model){
                foreach ($model as $v) {
                    $res = self::prDates($res, $v->started_at, $v->ended_at);
                }
            }
        }
        return ($res) ? $res : ['1900/01/01'];
    }

    static public function getMaxDate($class_period_id, $class_id, $started_at, $id = 0)
    {
        if($class_period_id && $class_id && $started_at){  
            $query = static::find()->andFilterWhere(['class_period_id' => $class_period_id, 'class_id' => $class_id , 'status' => self::STATUS_ACTIVE]);
            if($id){
                $query->andFilterWhere(['!=', 'id', $id]);
            }
            $min = $query->andFilterWhere(['>', 'started_at', $started_at])->min('started_at');
            $max = ($min) ? date('Y/m/d', strtotime('-1 day', $min)) : '2040/12/31';
            return $max;
        }
    }

    /**
     * 求两个日期之间的所有日期
     * @param string $started_at
     * @param string $ended_at
     */
    static public function prDates($res, $started_at, $ended_at){
        while ($started_at <= $ended_at){
            $res[] = date('Y/m/d', $started_at);
            $started_at = strtotime('+1 day', $started_at);
        }
        return $res;
    }

    //判断时间类型(日,月,年)
    static public function getDateType($query, $started_at)
    {
        $tableName = Course::tableName();
        $date = explode('/', $started_at);
        if (count($date) == 3) {
            $started_at = strtotime($started_at);
            $query->andFilterWhere(['<=', $tableName . '.started_at', $started_at])
                  ->andFilterWhere(['>=', $tableName . '.ended_at', $started_at])
                  ->orderBy($tableName . '.class_period_id,' . $tableName . '.id');
        } else if (count($date) == 2) {
            $time = self::getMouth($date);
            $query->andFilterWhere(['>=', $tableName . '.started_at', $time['started_at']])
                  ->andFilterWhere(['<=', $tableName . '.ended_at', $time['ended_at']])
                  ->orderBy($tableName . '.started_at, ' . $tableName . '.class_period_id, ' . $tableName . '.id');
        }
        return $query;
    }

    //获取月初和月末的时间戳
    static public function getMouth($date)
    {
        $startDay = $date[0] . '-' . $date[1] . '-1';
        $endDay = $date[0] . '-' . $date[1] . '-' . date('t', strtotime($startDay));
        return [
            'started_at' => strtotime($startDay),
            'ended_at' => strtotime($endDay)
        ];
    }

    public function getClassPeriods()
    {
        return $this->hasOne(ClassPeriod::className(), ['id' => 'class_period_id'])->alias('class_periods');
    }

    public function getClasses()
    {
        return $this->hasOne(Classes::className(), ['id' => 'class_id'])->alias('classes');
    }

    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }

    public function getInstructors()
    {
        return $this->hasOne(Admin::className(), ['id' => 'instructor'])->alias('instructors');
    }

    public function getInstructionMethods()
    {
        return $this->hasOne(InstructionMethod::className(), ['id' => 'instruction_method_id'])->alias('instruction_methods');
    }

    public function getCourseMaterials()
    {
        return $this->hasOne(CourseMaterial::className(), ['id' => 'course_material_id'])->alias('course_materials');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }

    public function getCount($type) {
        $groups = Group::findAll([
            'course_material_id' => $this->courseMaterials->id, 
            'type' => $type, 
            'status' => self::STATUS_ACTIVE
        ]);
        
        foreach ($groups as $value) {
            $material_library_id .= $value->material_library_id . ',';
        }
        $ids = explode(',', $material_library_id);

        return count(array_filter($ids));
    }


    public function getMaterials($table, $dir, $field, $type){
        $groups = Group::findAll([
            'course_material_id' => $this->courseMaterials->id, 
            'type' => $type, 
            'status' => self::STATUS_ACTIVE
        ]);

        foreach ($groups as $value) {
            $material_library_id .= $value->material_library_id . ',';
        }
        $ids = explode(',', $material_library_id);
        $model = $table::findAll($ids);
        $res = [];
        if($model){
            foreach ($model as $v) {
                //判断图片来源
                $studio = Campus::findOne(Admin::findOne($v->admin_id)->campus_id)->studio_id;
                $image = ($v->source == $table::SOURCE_LOCAL) ? Oss::getUrl($studio, $dir, $field, $v->$field) : $v->$field;
                if($type == Group::TYPE_PICTURE){
                    $res[] = [
                        'image_id' => $v->id,
                        'image' => $image.Yii::$app->params['oss']['Size']['250x250'],
                        'image_2x' => $image.Yii::$app->params['oss']['Size']['500x500'],
                    ];
                }else{
                    $res[] = [
                        'video_id'        => $v->id,
                        'title'           => $v->name,
                        'charging_option' => ($v->charging_option)/10,
                        'cc_id'           => $v->cc_id,
                        'preview'         => $image.Yii::$app->params['oss']['Size']['375x250'],
                        'preview_image'   => $image.Yii::$app->params['oss']['Size']['375x250'],
                    ];
                }
            }
        }
        return ($res) ? $res : [];
    }

}