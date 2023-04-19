<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\UserHomework;
use api\modules\v1\models\SendMessage;
use common\models\UserClass;
use common\models\Format;
use components\Upload;
use common\models\User;
use common\models\Campus;

class UserhomeworkController extends MainController
{
    public $modelClass = 'api\modules\v1\models\UserHomework';
	
    /**
     * [actionIndex 获取作业列表]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-21
     * @param     [type]        $user_id            [用户ID]
     * @param     integer       $course_material_id [教案ID 获取用户作业时不传]
     * @param     integer       $type               [类型 自己 or 他人]
     */
    public function actionIndex($user_id, $course_material_id = 0, $type = 10)
    {
        $modelClass = $this->modelClass;

        $query = $modelClass::find()->andFilterWhere(['status' => $modelClass::STATUS_ACTIVE]);
        
        //获取教案作业列表时,追加条件
        if(!empty($course_material_id)){
            $query->andFilterWhere(['course_material_id' => $course_material_id]); 
        }
        
        if($type == $modelClass::SCOPE_ME){
            $query->andFilterWhere(['user_id' => $user_id]);
            $query->orderBy('created_at DESC, id DESC');
        }else{
            $query->andFilterWhere(['!=', 'user_id', $user_id]);
            $query->orderBy('score DESC, created_at DESC');
        }

        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 99,
            ]
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if($model){
            $_GET['message'] = Yii::t('api', 'View Success');
            return $model;
        }else{
            return SendMessage::sendErrorMsg(Yii::t('api', 'User Homework Not Exist'));
        }
    }

	//上传作业
	public function actionCreate()
    {
        $modelClass = $this->modelClass;
        $model = new UserHomework(['scenario' => 'create']);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $studio = Campus::findOne(User::findOne($model->user_id)->campus_id)->studio_id;
        $model->image = Upload::pic_upload($_FILES, $studio, 'user-homework', 'image')['image'];

        if ($model->save()) {
            $_GET['message'] = Yii::t('api', 'UserHomework Create Success');
            return $modelClass::findOne($model->id);
        } else {
            return SendMessage::sendVerifyErrorMsg($model, Yii::t('api', 'Verify Error'));
        }
    }

    /**
     *[作业信息]
     *@param id[作业id] user_id [学生id]
     *@param 
     *
    */
    public function actionUserInfo($user_id)
    {   
        $modelClass = $this->modelClass;
        $query = $modelClass::find()
                    ->where(['user_id'=>$user_id,'status'=>$modelClass::STATUS_ACTIVE]);
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }


    protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        return (($model = $modelClass::findOne($id)) !== null) ? $model : false;
    }
}