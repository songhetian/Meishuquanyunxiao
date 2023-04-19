<?php

namespace teacher\modules\v1\models;

use Yii;
use backend\models\ActiveRecord;
use backend\models\Admin;
use common\models\Campus;
use teacher\modules\v1\models\Message;
use common\models\MessageCategory;
use common\models\CourseMaterial;
use common\models\Format;
use components\Jpush;
use components\Oss;
use components\Spark;

/**
 * This is the model class for table "{{%user_homework}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $course_material_id
 * @property string $image
 * @property integer $evaluator
 * @property string $comments
 * @property double $score
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class UserHomework extends ActiveRecord
{

    const SCOPE_ME = 10;
    const SCOPE_OTHER = 20;

    public static function tableName()
    {
        return '{{%user_homework}}';
    }
    public function fields()
    {
        $fields = parent::fields();

        $fields['homework_id'] = function() {
            return $this->id;
        };

        // if($this->users){
        //     $fields['user_id'] = function () {
        //         return [
        //             'id' => $this->users->id,
        //             'name' => $this->users->name
        //         ];
        //     };
        // }
        if(!empty($this->image)){
            $fields['image'] = function () {
                $size = Yii::$app->params['oss']['Size']['350x350'];
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->image).$size;
            };
            $fields['image_original'] = function () {
                $size = Yii::$app->params['oss']['Size']['original'];
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->image).$size;
            };
        }
        if(!empty($this->comment_image)){
            $fields['comment_image'] = function () {
                $size = Yii::$app->params['oss']['Size']['350x350'];
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->comment_image).$size;
            };
            $fields['comment_image_original'] = function () {
                $size = Yii::$app->params['oss']['Size']['original'];
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->comment_image).$size;
            };
        }
        if(!empty($this->video)){
            $fields['video'] = function () {
                return (Object)[
                    'cc_id' => $this->video,
                    'charging_option' => $this->charging_option,
                    'duration' =>  Spark::getDuration($this->video, $this->charging_option*10),
                ];
            };
        }

        $fields['user_name'] = function () {

            return $this->users->name;
        };

        $fields['created_at'] = function () {
            return date("Y.m.d", $this->created_at);
        };
        if(!empty($this->evaluator)){
            $fields['evaluator'] = function () {
                return[
                    'evaluator_id' => $this->evaluators->id,
                    'name'        => $this->evaluators->name
                ];
            };
        }
        unset(
            $fields['course_material_id'],
            $fields['evaluator'],
            $fields['updated_at'], 
            $fields['status'],
            $fields['cc_id'],
            $fields['id'],
            $fields['user_id']
        );
        return $fields;
    }

    public function beforeSave($insert)
    {
        //公共处理
        // $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
        // $this->image = Oss::upload($this, $studio, 'user-homework', 'image');
        if (parent::beforeSave($insert)) {
            //新数据插入
            if ($this->isNewRecord) {

            }else{
                //数据更新
                if ($this->image && $this->image != $this->getOldAttribute('image')) {
                    Oss::delFile($studio, 'user-homework', 'image', $this->getOldAttribute('image'));
                }else{
                    $this->image = $this->getOldAttribute('image');
                }
            }
            return true;
        }
        return false;
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        if($this->evaluator){
            $model = User::findIdentity($this->user_id);
            if($model){
                $title = '你的作品已被老师点评,快去看看吧!';

                $category = MessageCategory::findOne(['name' => Yii::t('common', 'My Msg')]);
                if($category){
                    $message = new Message();
                    $message->message_category_id = $category->id;
                    $message->campus_id = Format::explodeValue($model->campus_id);
                    $message->user_id = $this->user_id;
                    $message->title = $title;
                    $message->admin_id = $this->evaluator;
                    $message->content = $this->comments;
                    $message->correlated_id = $this->id;
                    $message->code = 2000;
                    $message->save();
                }
            }
        }
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['user_id', 'course_id'], 'required', 'on' => ['create', 'update']],
            [['comments', 'score'], 'required', 'on' => ['update']],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['charging_option', 'default', 'value' => 1],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['user_id', 'course_id', 'evaluator', 'created_at', 'updated_at', 'status','charging_option','course_id'], 'integer'],
            [['comments','score','video'], 'string'],
           # [['score'], 'number'],
            ['image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
            ],
            ['comment_image', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'minWidth' => 150,
                'minHeight' => 150,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户'),
            'course_material_id' => Yii::t('app', '所属教案'),
            'image' => Yii::t('app', '图片'),
            'evaluator' => Yii::t('app', '点评讲师'),
            'comments' => Yii::t('app', '评语'),
            'score' => Yii::t('app', '得分'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'course_id'  => '所属课程',
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function GetStudio($admin_id) {

        return Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
    }

    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->alias('users');
    }

    public function getCourseMaterials()
    {
        return $this->hasOne(CourseMaterial::className(), ['id' => 'course_material_id'])->alias('course_materials');
    }

    public function getEvaluators()
    {
        return $this->hasOne(Admin::className(), ['id' => 'evaluator'])->alias('evaluators');
    }
}
