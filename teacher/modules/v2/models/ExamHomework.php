<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\helpers\ArrayHelper;
use components\Oss;
use common\models\Exam;
use common\models\Format;
use common\models\ExamReview;
use common\models\ExamHomeworkReview;

class ExamHomework extends \common\models\ExamHomework
{
    public function fields()
	{
	    $fields = parent::fields();

        if($this->image){
            $fields['image'] = function () {
                $size = Yii::$app->params['oss']['Size']['fix_width'];
                return Oss::getUrl($_GET['studio_id'], 'exam', 'homework', $this->image).$size;
            };
            $fields['original_image'] = function () {
                $size = Yii::$app->params['oss']['Size']['original'];
                return Oss::getUrl($_GET['studio_id'], 'exam', 'homework', $this->image).$size;
            };
        }

        $fields['score'] = function () {
            return ($this->score) ? $this->score . '分' : $this->score;
        };

        if($_GET['source'] == 10){
        	unset(
        		$fields['score'],
        		$fields['review_state']
        	);
        }elseif($_GET['source']== 30){
            $fields['user_name'] = function () {
                $user = User::findOne($this->user_id);
                return $user->name;
            };
            $fields['user_class'] = function () {
                $user = User::findOne($this->user_id);
                return Classes::findOne($user->class_id)->name;
            };
            unset(
                $fields['review_state']
            );
        }
        
	    unset(
        	$fields['exam_id'],
        	$fields['user_id'],
        	$fields['review_id'],
            $fields['review_at'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
        
	    return $fields;
	}

    //获取可见班级
    public static function getCreateClasses($account_id) {
        $admin = Admin::findOne($account_id);
        $campuses =  Format::explodeValue($admin->campus_id);
        $ids = Format::explodeValue($admin->class_id);
        $model =  Classes::find()
        ->andFilterwhere(['id' => $ids, 'campus_id'=> $campuses, 'status' => Classes::STATUS_ACTIVE])
        ->all();
        return ($model) ? ArrayHelper::map($model, 'id', 'name') : [];
    }

    //获取过滤条件列表
    public static function getFilter($id, $account)
    {
        $class[] = ['title' => '全部班级', 'class_id' => '001'];
        $class_id = Exam::findOne($id)->class_id;
        $classes = Format::explodeValue($class_id);
        foreach ($classes as $v) {
            $class[] = [
                'title' => Classes::findOne($v)->name,
                'class_id' => $v
            ];
        }

        $states = self::getValues('review_state');
        $state[] = ['title' => '全部状态', 'state_id' => '002'];
        foreach ($states as $k => $v) {
            $state[] = [
                'title' => $v,
                'state_id' => (string)$k
            ];
        }

        $sort = [
            [
                'title' => '默认排序',
                'sort_id' => '003'
            ],
            [
                'title' => '分数从高到低',
                'sort_id' => 'score DESC'
            ],
            [
                'title' => '分数从低到高',
                'sort_id' => 'score ASC'
            ],
            [
                'title' => '上传时间从早到晚',
                'sort_id' => 'created_at ASC'
            ],
            [
                'title' => '上传时间从晚到早',
                'sort_id' => 'created_at DESC'
            ],
        ];
        return [
            [
                'type' => 'class',
                'selectedIndex' => 0,
                'data' => $class
            ],
            [
                'type' => 'state',
                'selectedIndex' => 0,
                'data' => $state
            ],
            [
                'type' => 'sort',
                'selectedIndex' => 0,
                'data' => $sort
            ]
        ];
    }

    //修改批阅状态和平均分
    public static function updateHomeWork($id) {
        $model = ExamHomework::findOne($id);

        $count = ExamReview::find()->where(['exam_id' => $model->exam_id, 'status' => ExamReview::STATUS_ACTIVE])->count();

        $query = ExamHomeworkReview::find()->where(['homework_id' => $id]);

        $model->score = $query->average('score');
        if($query->count() == $count){
            $model->review_state = ExamHomework::REVIEW_STATE_ED;
        }

        if($model->save()){
            return $model;
        }
    }
}