<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;

class MainController extends ActiveController
{
	/**
	 * [$serializer 数据分页]
	 * @var [type]
	 */
	/*
    public $serializer = [
         'class' => 'yii\rest\Serializer',
         'collectionEnvelope' => 'items'
    ];
	*/

    /**
     * [behaviors 设置返回格式为JSON]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     */
    public function behaviors()
	{
	    $behaviors = parent::behaviors();
	    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
	    return $behaviors;
	}

	/**
	 * [actions 注销系统自带的实现方法]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-03-21
	 */
    public function actions()
	{
		$actions = parent::actions();
	    unset(
	    	$actions['index'], 
	    	$actions['update'], 
	    	$actions['create'], 
	    	$actions['delete'], 
	    	$actions['view']
	    );
    	return $actions;
	}

	public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        $status = \components\Api::deleteValidation($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
    	if($status == false){
    		$_GET['message'] = Yii::t('common','ACCOUNT DISABLE');
			$_GET['error'] = 1001;
			$result = [];
    	}
        return $this->serializeData($result);
    }
}