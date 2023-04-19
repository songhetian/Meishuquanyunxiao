<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class CityController extends MainController
{
    public $modelClass = 'api\modules\v1\models\City';
	
    /**
     * [actionIndex 获取城市列表]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     * @param     integer       $pid [父ID]
     */
	public function actionIndex($pid = 0)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where([
            'pid' => $pid, 
            'status' => $modelClass::STATUS_ACTIVE
        ]);
        
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
	            'pagesize' => 34,
	        ]
        ]);
    }
}