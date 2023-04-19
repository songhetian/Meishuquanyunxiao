<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;

class MessageCategoryController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\MessageCategory';
	
    /**
     * [actionIndex 获取消息分类列表]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     * @param     integer       $user_id [用户ID]
     */
	public function actionIndex($user_id)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->where(['status' => $modelClass::STATUS_ACTIVE]);

        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 99,
            ]
        ]);
    }
}