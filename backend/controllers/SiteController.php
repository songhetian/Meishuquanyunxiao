<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use backend\models\Admin;
use backend\models\LoginForm;
use common\models\User;
use common\models\Campus;
use common\models\Format;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $campus_id = Campus::getCampuses(Format::getStudio('id'));
        $count = [
            //审核用户数量
            'user' => User::find()
                ->andFilterWhere(['campus_id' => $campus_id, 'is_review' => User::REVIEW_ED])
                ->count()
        ];

        return $this->render('index',[
            'count' => $count, 
        ]);
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        Yii::$app->session->setFlash('studio',Yii::$app->request->get('name'));

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->renderPartial('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        $name = Format::getStudio('name');
        Yii::$app->user->logout(false);
        return $this->redirect(['site/login', 'name' => $name]);
    }
}
