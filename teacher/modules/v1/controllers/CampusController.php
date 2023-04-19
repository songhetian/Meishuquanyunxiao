<?php
namespace teacher\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v1\models\Admin;
use common\models\Format;

class CampusController extends MainController
{
    public $modelClass = 'teacher\modules\v1\models\Campus';
	
    /**
     * [actionIndex 获取校区列表]
     * @copyright [tian]
     * @version   [v1.0]
     * @date      2017-03-20
     */
	public function actionIndex($studio_id,$admin_id)
    {
        $admin      = Admin::findOne($admin_id);
        $campus_id  = Format::explodeValue($admin['campus_id']);
        $class_id   = Format::explodeValue($admin['class_id']);
       
        $modelClass = $this->modelClass; 
        $modelClass::$list = $class_id;

        $list = $modelClass::find()
                            ->where(['studio_id' => $studio_id, 'status' => $modelClass::STATUS_ACTIVE])
                            ->andFilterWhere(['id'=>$campus_id])
                            ->all();
        foreach ($list as $key => $value) {
            if(!$value->classes) {
                unset($list[$key]);
            }
        }
        $_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
        
        return $list;
    }
}