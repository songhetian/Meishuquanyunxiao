<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use teacher\modules\v2\models\Course;
use backend\models\ActiveRecord;

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
class SimpleCourse extends ActiveRecord
{
    static $is_view = 1;
    static $user_id = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course}}';
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['started_at'] = function () {
            return date("Y/m/d", $this->started_at);
        };

        $fields['ended_at'] = function () {
            return date("Y/m/d", $this->ended_at);
        };
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

        if(!$this->class_content){
            $fields['class_content'] = function() {

                return  $this->courseMaterials->name;
            };
        }

        if($this->classes){
            $fields['class'] = function () {
                return [
                    'class_id' => $this->classes->id
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
                    return [
                        'course_material_id' => $this->courseMaterials->id,
                        'admin_id'           => $admin_id,
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
                    return [
                        'course_material_id' => $this->courseMaterials->id,
                        'admin_id'           => $admin_id,
                        'picture_count' => $this->getCount(Group::TYPE_PICTURE),
                        'video_count'   => $this->getCount(Group::TYPE_VIDEO) ,
                    ];
                };  
            }
        }
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
    //判断时间类型(日,月,年)
    static public function getDateType($started_at,$ended_at,$class_id,$type,$user_id,$studio_id)
    {

        $end_date = NULL;

        $start_date = NULL;

        if(in_array($studio_id, Yii::$app->params['NoYanZheng'])) {

            $studio_id =  Yii::$app->params['NoYanZheng'];


            $code =  ActivationCode::findOne(['type'=>$type/10,'relation_id'=>$user_id]);

           # $user  = User::findOne($user_id);

            $end_date = strtotime($code->due_time);

            $start_date  = strtotime(date("Y-m-d",$user->created_at));

            $tableName = self::tableName();
            $started_at = strtotime($started_at);
            $ended_at   = strtotime($ended_at);

            $list = self::find()
                  ->select('id')
                  ->where(['class_id' => $class_id,'status'=>self::STATUS_ACTIVE])
                  ->andFilterWhere(['>=', $tableName . '.started_at', $started_at])
                  ->andFilterWhere(['<=', $tableName . '.ended_at', $ended_at])
                  ->andFilterWhere(['between','started_at',$start_date, $end_date])
                  ->andFilterWhere(['between','ended_at',$start_date, $end_date])
                  ->orderBy($tableName . '.class_period_id,' . $tableName . '.id')
                  ->asArray()
                  ->all();
        }else{
                $tableName = self::tableName();
                $started_at = strtotime($started_at);
                $ended_at   = strtotime($ended_at);

                $list = self::find()
                      ->select('id')
                      ->where(['class_id' => $class_id,'status'=>self::STATUS_ACTIVE])
                      ->andFilterWhere(['<=', $tableName . '.started_at', $ended_at])
                      ->andFilterWhere(['>=', $tableName . '.ended_at', $started_at]) 
                      ->orderBy($tableName . '.class_period_id,' . $tableName . '.id')
                      ->asArray()
                      ->all();
        }
        
         return array_column($list, 'id');
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'started_at', 'ended_at', 'admin_id', 'created_at', 'updated_at'], 'required'],
            [['class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'course_material_id', 'started_at', 'ended_at', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['class_content', 'class_emphasis', 'note','prew_iamge'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class_period_id' => 'Class Period ID',
            'class_id' => 'Class ID',
            'category_id' => 'Category ID',
            'instructor' => 'Instructor',
            'instruction_method_id' => 'Instruction Method ID',
            'course_material_id' => 'Course Material ID',
            'started_at' => 'Started At',
            'ended_at' => 'Ended At',
            'class_content' => 'Class Content',
            'class_emphasis' => 'Class Emphasis',
            'note' => 'Note',
            'admin_id' => 'Admin ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
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

}
