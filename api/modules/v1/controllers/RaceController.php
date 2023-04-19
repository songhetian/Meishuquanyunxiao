<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class RaceController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Race';
	
	public function actionIndex()
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where(['status' => $modelClass::STATUS_ACTIVE]);
        
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 56,
            ]
        ]);
    }
}