<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\Format;
use common\models\Curl;
use common\models\CourseMaterial;

class PictureController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Picture';
	
	public function actionIndex($material_library_id)
    {
        $modelClass = $this->modelClass;

        $ids = Format::explodeValue($material_library_id);

        $query = $modelClass::find()->where(['id' => $ids, 'status' => $modelClass::STATUS_ACTIVE]);

        //添加查看次数
        $modelClass::updateAllCounters(['watch_count' => 1], ['id' => $ids]);

        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
	            'pagesize' => -1,
	        ]
        ]);
    }
    public function actionGetmeis($type = 10, $category_id = 0, $category_child_id = 0, $keyword_id = 0, $publishing_id = 0, $page = 0, $page_limit = 500)
    {
        $materials = CourseMaterial::getMetisMaterials($type,$category_id,$category_child_id,$keyword_id,$publishing_id,$page,$page_limit);

        $_GET['message'] = Yii::t('api', 'Pic Metis');
        return $materials;
    }

    public function actionSearch($search_input,$limit=10,$page=0) {

        $modelClass = $this->modelClass;
        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        return $modelClass::Serach($search_input,$limit,$page);
    }
}