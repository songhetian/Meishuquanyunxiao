<?php
namespace teacher\modules\v1\controllers;

use Yii;
use teacher\modules\v1\models\LoginForm;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\SendMessage;

class AdminController extends MainController
{
    public $modelClass = 'teacher\modules\v1\models\Admin';
	
	/*
	*[actionLogin]  登陆接口
	*@param 
	*
	*/

    public function actionLogin() 
    {
        $model = new LoginForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if($admin = $model->login())
        {
            $_GET['message'] = Yii::t('teacher', 'Login Success');
            return $this->findModel($admin->id);
        }
         return SendMessage::sendErrorMsg(Yii::t('teacher', 'Password Fail'));
    }
    /**
     * [退出登陆]
     *
     * @param $id 用户id
     *
     */
    public function actionLogout($admin_id)
    {
        $model = $this->findModel($admin_id);
        if($model){
            return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Logout Success'));
        }else{
            return SendMessage::sendErrorMsg(Yii::t('teacher', 'User Not Exist'));
        }
    }

    /*
     *[获取可见范围用户信息]
     *
     *
    */
    public function actionGetList($admin_id)
    {
        $modelClass = $this->modelClass;

        $list = $modelClass::getVisua($admin_id);

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        
        return $list;
    }

    public function findModel($id) 
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}