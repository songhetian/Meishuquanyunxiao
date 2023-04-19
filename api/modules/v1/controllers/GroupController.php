<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class GroupController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Group';
	
    /**
     * [actionIndex 获取教案分组]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     * @param     integer       $course_material_id [教案ID]
     * @param     integer       $type               [类型]
     */
	public function actionIndex($course_material_id, $type)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where([
            'course_material_id' => $course_material_id, 
            'type' => $type, 
            'status' => $modelClass::STATUS_ACTIVE
        ]);
       
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
	            'pagesize' => 99,
	        ]
        ]);
    }
}