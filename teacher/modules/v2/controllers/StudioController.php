<?php
namespace teacher\modules\v2\controllers;

use Yii;
use components\Oss;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\Collection;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;

class StudioController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Studio';
	
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
     * @Author    TianHeSong
     * @DateTime  2020-07-08
     * @copyright [获取所有画室]
     * @license   [license]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function actionList() {

        $array =  [183,143];
        $list =  \common\models\Studio::find()
                    ->select(['studio_id'=>'id','name'])
                    ->where(['status' => Studio::STATUS_ACTIVE])
                    ->andWhere(['NOT IN','id',$array])
                    ->asArray()
                    ->all();
        $_GET['message'] = '获取信息成功';
        return $list;
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


    //获取审核状态
    public function actionExamine($version = 0) {

        $_GET['message'] = "获取成功";  
        if($version == Yii::$app->params['Version']) {
            return 1;
        }else{
            return 0;
        }
    }

    /***
    *[按照时间段删除画室学生]
    * @copyright [tian]
    * @version   [v2.0]
    * @date      2018-06-11
    * @param     integer $studio [画室ID] string $time[时间段]
    *
    */
    public function actionDeleteStudio($studio,$time)
    {
        $campus   =  array_keys(Campus::find()->where(['studio_id'=>$studio])->indexBy('id')->all());

        $students =  array_keys(User::find()
                           ->where(['campus_id'=>$campus])
                           ->andWhere(['<','created_at',strtotime($time)])
                           ->indexBy('id')
                           ->all());
        $connect = Yii::$app->db->beginTransaction();

        try{
            if(!User::updateAll(['status' => 0], ['id'=>$students])) {
                throw new ErrorException("学生删除失败!");  
            }
            if(!ActivationCode::updateAll(['status' => 0], ['relation_id'=>$students,'type'=>2])) {
                throw new ErrorException("激活码失败!");  
            }
            $connect->commit();
            return SendMessage::sendSuccessMsg("都删了");
        } catch (ErrorException $e) {
            $connect->rollBack();
            return SendMessage::sendErrorMsg($e->getMessage());
        }
    }

    public function actionGetInfo($studio_id) {

       $studio = Studio::findOne(['id' => $studio_id, 'status' => Studio::STATUS_ACTIVE]);

       $token_value  =  $studio->token_value;

       $fileName = 'logo.png';

       $_GET['message']  =  "获取信息成功";

       return OSS::getUrl($studio_id, 'download', 'app', $fileName).Yii::$app->params['oss']['Size']['500x500'];
    }


    /**
     *[机构信息录入]
     *
     *
     *
    */

    public function actionEntry () {

        $model = new Collection();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if($model->save()) {
            return SendMessage::sendSuccessMsg("画室入驻成功,等待审核.");
        }else{
            return SendMessage::sendErrorMsg("您已提交入驻申请,请等待审核。");
        }
    }

}