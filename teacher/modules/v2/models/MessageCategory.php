<?php

namespace teacher\modules\v2\models;

use Yii;
use common\models\Message;

class MessageCategory extends \common\models\MessageCategory
{

    public function fields()
	{
	    $fields = parent::fields();

	    $user_id = Yii::$app->request->get('user_id');
	    
	    $fields['icon'] = function (){
	    	return Yii::$app->request->hostInfo.'/assets/images/icon/'.$this->icon;
	    };
	    
	    $fields['description'] = function (){
	    	return $this->getDescription($user_id);
	    };
	    
	    unset(
	    	$fields['priority'],
	    	$fields['created_at'], 
	    	$fields['updated_at'], 
	    	$fields['status']
	    );
	    return $fields;
	}

	/**
	 * [getDescription 获取最后一条数据作为简介]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-03-21
	 * @param     [type]        $user_id [用户ID]
	 */
	function getDescription($user_id)
	{
		$query = Message::find()->andFilterWhere([
    		'message_category_id' => $this->id,
    		'status' => Message::STATUS_ACTIVE
    	]);

		if($this->name == Yii::t('common', 'My Msg')){
            $query->andFilterWhere(['user_id' => $user_id]);
        }

        $model = $query->orderBy('created_at DESC, id DESC')->one();
        return ($model) ? $model->title : NULL;
	}
}
