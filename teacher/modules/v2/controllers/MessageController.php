<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\User;
use common\models\Format;
use common\models\MessageCategory;

class MessageController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Message';
	
    /**
     * [actionIndex 获取消息列表]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     * @param     [type]        $message_category_id [消息分类ID]
     * @param     [type]        $user_id             [用户ID]
     */
    public function actionIndex($message_category_id, $user_id)
    {
        $modelClass = $this->modelClass;

        $campus_id = User::findOne($user_id)->campus_id;

        $query = $modelClass::find();

        $query->andFilterWhere([
            'message_category_id' => $message_category_id, 
            'status' => $modelClass::STATUS_ACTIVE
        ]);

        $query->andFilterWhere(['or like', Format::concatField('campus_id'), Format::concatString($campus_id)]);
               
        $category = MessageCategory::findOne($message_category_id);
        
        if($category->name == Yii::t('common', 'My Msg')){
            $query->andFilterWhere(['user_id' => $user_id]);
        }

        $query->orderBy('created_at DESC, id DESC');
        
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 99,
            ]
        ]);
    }
}