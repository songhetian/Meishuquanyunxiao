<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class CampusController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Campus';
	
    /**
     * [actionIndex 获取校区列表]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-20
     */
	public function actionIndex($studio_id)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where(['studio_id' => $studio_id, 'status' => $modelClass::STATUS_ACTIVE]);

        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }
}