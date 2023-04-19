<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use backend\models\Menu;
use backend\models\Rbac;

class Controller extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            // 后台必须登录才能使用
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        // 指定该规则是 "允许" 还是 "拒绝"
                        'allow' => true,
                        // @ 已认证用户
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    public function actions()
    {
        parent::actions();
        $this->authCan();
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
   
    public function authCan()
    {
        //未登录时不验证
        if(!isset(Yii::$app->user->identity->id)){
            return;
        }

        //获取Rbac名称
        $defaultRoute = Yii::$app->controller->module->defaultRoute;
        $name = $this->getRbacName($defaultRoute);
        if($name == false){
            return;
        }

        //判断RBAC中是否有该权限
        $model = Rbac::findOne(['name' => $name]);
        if(!$model){
            return;
        }

        if(!Yii::$app->user->can($name)){
            if($name == $defaultRoute){
                $this->redirect([$this->getRedirectUrl()]);
                Yii::$app->end();
            }else{
                $this->redirect(['site/error', 'type' => 'permission']);
                Yii::$app->end();
            }
        }
    }

    public function getRbacName($defaultRoute){
        $pathInfo = Yii::$app->request->getPathInfo();
        if($pathInfo){
            $suffix = Yii::$app->controller->module->urlManager->suffix;
            //判断是否包含符合规范的后缀名
            if(!strpos($pathInfo,$suffix) === false){
                $path = str_replace($suffix, '', $pathInfo);
                $exp = explode('/', $path);
                return (end($exp) == 'index') ? $exp[0] : $path;
            }
        }else{
            return $defaultRoute;
        }
        return false;
    }

    //跳转有权限的菜单
    public function getRedirectUrl()
    {
        foreach (Menu::menuList()as $value) {
            foreach (Menu::menuList($value->id) as $v) {
                if(Yii::$app->user->can($v->route)){
                    return $v->route . '/index';
                }
            }
        }
        return false;
    }
}