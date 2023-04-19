<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\Tool;
use teacher\modules\v1\models\Course;
use teacher\modules\v1\models\Admin;
use teacher\modules\v1\models\ClassPeriod;  
use teacher\modules\v1\models\Group;
use teacher\modules\v1\models\SimpleCourse;
use teacher\modules\v1\models\CourseMaterial;
use teacher\modules\v1\models\CourseCutInfo;
use teacher\modules\v1\models\CourseMaterialInfo;



class CourseController extends MainController
{
	public $modelClass = 'api\modules\v1\models\Course';

    /**
     * [actionIndex 获取课程表]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-20
     * @param     [type]        $class_id   [班级ID]
     * @param     [type]        $started_at [时间 支持（[2017/03] [2017/03/09]）两种查询]
     */
    public function actionIndex($class_id, $started_at)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->andFilterWhere([
            'class_id' => $class_id, 
            'status' => $modelClass::STATUS_ACTIVE
        ]);
        
        $query = $modelClass::getDateType($query, $started_at);
        
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }
        $_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
        return array_values($array);
    }
}