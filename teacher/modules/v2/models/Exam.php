<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\helpers\ArrayHelper;
use components\Oss;
use common\models\Query;
use common\models\Category;
use common\models\Format;
use common\models\Classes;
use common\models\ExamReview;
use common\models\ExamHomework;

class Exam extends \common\models\Exam
{
    public $original_image;
    //角标
    const TYPE_RELEASE_NOT_YET = 10;
    const TYPE_START_NOTE_YET = 20;
    const TYPE_EXAMINATION = 30;
    const TYPE_REVIEWING = 40;
    const TYPE_SEARCH_RESULT = 50;

    //详情内按钮
    const ICON_TYPE_RELEASE_ED = 10;
    const ICON_TYPE_RELEASE_CANCEL = 20;
    const ICON_TYPE_EDITOR = 30;
    const ICON_TYPE_DELETE = 40;
    const ICON_TYPE_UPLOAD = 50;
    const ICON_TYPE_REVIEW = 60;
    const ICON_TYPE_SEARCH_RESULT = 70;

    public function fields()
	{
	    $fields = parent::fields();

        $fields['title'] = function() {
            if($_GET['source'] == 10){
                if(time() < $this->time){
                    if($_GET['user_role'] != 'teacher'){
                        return '**********';
                    }
                }
            }
            return $this->title;
        };

        $fields['time'] = function () {
            return date('Y-m-d H:i:s', $this->time);
        };

        $fields['type'] = function () {
            return Exam::getValues('type', $this->type);
        };

        if($this->classes){
            $fields['class_id'] = function () {
                return Query::concatValue(Classes::className(), $this->class_id, true);
            };
        }

        if($this->categorys){
            $fields['category_id'] = function () {
                return $this->categorys->name;
            };
        }

		$fields['length'] = function () {
            return $this->length . '分钟';
        };

        if($this->image){
            $fields['image'] = function () {
                $size = Yii::$app->params['oss']['Size']['fix_width'];
                if($_GET['source'] == 10){
                    if(time() < $this->time){
                        if($_GET['user_role'] != 'teacher'){
                            return 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/exm.jpg'.$size;
                        }
                    }
                }
                $studio_id = ActivationCode::findOne(['relation_id' => $this->admin_id, 'type' => 1])->studio_id;
                return Oss::getUrl($studio_id, 'exam', 'image', $this->image).$size;
            };
            $fields['original_image'] = function () {
                $size = Yii::$app->params['oss']['Size']['original'];
                if($_GET['source'] == 10){
                    if(time() < $this->time){
                        if($_GET['user_role'] != 'teacher'){
                            return 'http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/exm.jpg'.$size;
                        }
                    }
                }
                $studio_id = ActivationCode::findOne(['relation_id' => $this->admin_id, 'type' => 1])->studio_id;
                return Oss::getUrl($studio_id, 'exam', 'image', $this->image).$size;
            };
        }
        
        $fields['release_state'] = function () {
            return ($this->release_state == self::RELEASE_STATE_NOT_YET) ? '未发布' : '已发布';
        };

        if($_GET['source'] == 10){
            $fields['superscript'] = function () {
                return self::getSuperscript($this);
            };
        	unset(
        		$fields['category_id'],
        		$fields['content'],
        		$fields['require'],
                $fields['release_state']
        	);
        }
        
	    unset(
            $fields['admin_id'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
        
	    return $fields;
	}

	//获取考试列表[已发布 + 自己未发布]
    public static function createData($user_role, $account, $page, $limit){
        $query = self::find();

        switch ($user_role) {
            case 'student':
                $class_id = $account->class_id;
                $query->andFilterwhere([
                    'release_state' => self::RELEASE_STATE_ED
                ]);
                break;

            case 'teacher':
                $class_id = self::getCreateClasses($account->id, 20);
                $query->andFilterwhere([
                    'or',
                    ['release_state' => self::RELEASE_STATE_ED],
                    ['release_state' => self::RELEASE_STATE_NOT_YET, 'admin_id' => $account->id]
                ]);
                break;

            case 'family':
                $class_id = User::findOne($account->relation_id)->class_id;
                $query->andFilterwhere([
                    'release_state' => self::RELEASE_STATE_ED
                ]);
                break;
        }

        $model = $query->andWhere([
            'or like',
            Format::concatField('class_id'),
            Format::concatString($class_id)
        ])
        ->andFilterwhere([
            'status' => self::STATUS_ACTIVE
        ])
        ->offset($page * $limit)
        ->limit($limit)
        ->orderBy('created_at DESC')
        ->all();
        return $model;
    }

	//获取科目
	public static function getCreateCategorys()
	{
		$model = Category::findAll(['pid' => 0, 'type' => Category::TYPE_VIDEO, 'status' => Category::STATUS_ACTIVE]);
        return ($model) ? ArrayHelper::map($model, 'id', 'name') : [];
	}

	//获取可见班级
    public static function getCreateClasses($account_id, $type = 10) {
    	$admin = Admin::findOne($account_id);
        $campuses =  Format::explodeValue($admin->campus_id);
        $ids = Format::explodeValue($admin->class_id);
        $query = Classes::find();
        $query->andFilterwhere(['id' => $ids, 'campus_id'=> $campuses, 'status' => Classes::STATUS_ACTIVE]);
        if($type == 10){
            $model = $query->all();
            return ($model) ? ArrayHelper::map($model, 'id', 'name') : [];
        }else{
            $model = $query->indexBy('id')->all();
            return ($model) ? array_keys($model) : [];
        }
    }

    //获取阅卷人列表
    public static function getCreateExams($account_id) {
    	$admin = Admin::findOne($account_id);
    	$model = Admin::find()
    	->andFilterwhere([
			'or like',
			Format::concatField('campus_id'),
			Format::concatString($admin->campus_id)
		])
		->andFilterWhere(['NOT', ['name' => 'NULL']]) 
		->andFilterWhere(['status' => Admin::STATUS_ACTIVE]) 
		->all();
		return ($model) ? ArrayHelper::map($model, 'id', 'name') : [];
    }

    //获取角标信息
    public static function getSuperscript($model)
    {
        if($model->release_state == self::RELEASE_STATE_NOT_YET){
            $type = self::TYPE_RELEASE_NOT_YET;
            $name = '未发布';
            $color = '#00ac4d';
        }else{
            $time = time();
            if($time < $model->time){
                $type = self::TYPE_START_NOTE_YET;
                $name = '未开始';
                $color = '#ff0000';
            }else{
                $end_time = $model->time + 1 * $model->length * 60;
                if($time < $end_time){
                    $type = self::TYPE_EXAMINATION;
                    $name = '考试中';
                    $color = '#00b4ff';
                }else{
                    if($model->review_state == self::REVIEW_STATE_NOT_YET){
                        $type = self::TYPE_REVIEWING;
                        $name = '阅卷中';
                        $color = '#ffc700';
                    }else{
                        $type = self::TYPE_SEARCH_RESULT;
                        $name = '查成绩';
                        $color = '#44BC7D';
                    }
                }
            }
        }
        return [
            'type' => $type,
            'name' => $name,
            'color' => $color
        ];
    }

    public static function getIcons($type, $user_role, $account_id, $model)
    {
        $icons = [];
        switch ($type) {
            case self::TYPE_RELEASE_NOT_YET:
                if($user_role == 'teacher'){
                    $icons[] = [
                        'name' => '发布',
                        'type' => self::ICON_TYPE_RELEASE_ED
                    ];
                    $icons[] = [
                        'name' => '编辑',
                        'type' => self::ICON_TYPE_EDITOR
                    ];
                }
                break;
            case self::TYPE_START_NOTE_YET:
                if($user_role == 'teacher'){
                    if($account_id == $model->admin_id){
                        $icons[] = [
                        'name' => '取消发布',
                        'type' => self::ICON_TYPE_RELEASE_CANCEL
                        ];
                        $icons[] = [
                            'name' => '编辑',
                            'type' => self::ICON_TYPE_EDITOR
                        ];
                        $icons[] = [
                            'name' => '删除',
                            'type' => self::ICON_TYPE_DELETE
                        ];
                    }
                    
                }
                break;
            case self::TYPE_EXAMINATION:
                if($user_role == 'student'){
                    $icons[] = [
                        'name' => '上传作品',
                        'type' => self::ICON_TYPE_UPLOAD
                    ];
                }
                break;
            case self::TYPE_REVIEWING:
                if($user_role == 'teacher'){
                    //查询是否是当前考试的阅卷人
                    $isset = ExamReview::findOne([
                        'exam_id' => $model->id,
                        'review_id' => $account_id,
                        'status' => ExamReview::STATUS_ACTIVE
                    ]);
                    if($isset){
                        $icons[] = [
                            'name' => '阅卷',
                            'type' => self::ICON_TYPE_REVIEW
                        ];
                    }
                    $icons[] = [
                        'name' => '查成绩',
                        'type' => self::ICON_TYPE_SEARCH_RESULT
                    ];
                }
                break;
            case self::TYPE_SEARCH_RESULT:
                $icons[] = [
                    'name' => '查成绩',
                    'type' => self::ICON_TYPE_SEARCH_RESULT
                ];
                break;
        }
        return $icons;
    }
   //拼接班级
   public static function getClassesName($class_id) {

        $class = Format::explodeValue($class_id);

        return Format::implodeValue(array_keys(Classes::find()->where(['id'=>$class])
                       ->indexBy('name')
                       ->all()));
    }

    //拼接阅卷人
    public static function getReview($review_id) {

        $review = Format::explodeValue($review_id);

        return Format::implodeValue(array_keys(Admin::find()->where(['id'=>$review])
                       ->indexBy('name')
                       ->all()));
    }

}