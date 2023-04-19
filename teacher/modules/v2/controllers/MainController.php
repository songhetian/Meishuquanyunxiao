<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\web\Response;
use yii\rest\ActiveController;
use teacher\modules\v2\models\Tencat;

class MainController extends ActiveController
{

	public $user_role;
	public $user_type;
	public $user_id;
	public $studio_type; 
	public $studio_name;
	public $studio_id;
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
    public function beforeAction($action) {
    	parent::beforeAction($action);
    	$this->user_type = $_SERVER['HTTP_TYPE'];
    	$this->user_role = ($_SERVER['HTTP_TYPE'] == Yii::$app->params['teacherRole']) ? 10 : 20;
    	if($_SERVER['HTTP_TYPE'] == Yii::$app->params['familyRole']) {
    		$this->user_role =  30;
    	}
    	$this->user_id = \components\Api::getUser($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
    	$this->studio_type = \components\Api::getStudioType($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
    	$this->studio_name = \components\Api::getStudioName($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
    	$this->studio_id   = \components\Api::getStudioID($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
    	if($this->studio_type == 1) {


    	}
        return true;
    }
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
     * [定义方法的请求方式]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     */
	protected function verbs()
	  {
	    return [
	      'index' => ['GET', 'HEAD'],
	      'view' =>['GET', 'HEAD', 'POST'],
	      'create' =>['POST'],
	      'update' =>['PUT', 'PATCH','POST'],
	      'delete' =>['DELETE','POST','GET'],
	    ];
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
    	$status        = \components\Api::deleteValidation($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
    	#$expire_status = \components\Api::ExpireStatus($_SERVER['HTTP_TYPE'], $_SERVER['HTTP_TOKEN_VALUE']);
        $result = parent::afterAction($action, $result);
        if(Yii::$app->controller->action->id == 'login-test' || Yii::$app->controller->action->id == 'examine' || (Yii::$app->controller->id.'/'.Yii::$app->controller->action->id) == 'studio/list'){
        	$status = true;
        	#$expire_status == true;
        }
   //  	if($expire_status == false){
   //  		$_GET['message'] = "该账号已到期,请续费!";
			// $_GET['error'] = 1002;
			// $result = [];
   //  	}
    	if($status == false){
    		$_GET['message'] = Yii::t('common','ACCOUNT DISABLE');
			$_GET['error'] = 1001;
			$result = [];
    	}
        return $this->serializeData($result);
    }
}