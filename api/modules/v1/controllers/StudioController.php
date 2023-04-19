<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class StudioController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Studio';
	
    /**
     * [actionIndex 获取画室基本信息]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-20
     * @param     integer       $studio [画室ID]
     * @param     integer       $user_id [用户ID 获取对应可见范围数据]
     */
	public function actionIndex($studio_id, $user_id)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where(['id' => $studio_id, 'status' => $modelClass::STATUS_ACTIVE]);
        
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }

    /**
     * [actiongGetView 获取画室是否可见素材库]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-08-01
     * @param     integer       $studio [画室ID]
     * 
     */
    public function actionGetView($studio_id)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where(['id' => $studio_id, 'status' => $modelClass::STATUS_ACTIVE]);
        
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }
}