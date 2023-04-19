<?php

namespace backend\controllers;

use Yii;
use common\models\CommonProblem;
/**
 * BuyRecordController implements the CRUD actions for BuyRecord model.
 */
class WentiController extends \yii\web\Controller
{
    public $layout = false; //不使用布局
    /**
     * Lists all BuyRecord models.
     * @return mixed
     */
    public function actionIndex()
    {   

        $data = CommonProblem::findAll(['status'=>10]);

        return $this->render('index',['data'=>$data]);
    }

    public function actionInfo($id) {

        $data = CommonProblem::findOne($id);
        
    	return $this->render('info',['data'=>$data]);
    }
}
