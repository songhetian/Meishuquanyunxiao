<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use components\Oss;
use common\models\Curl;
use common\models\Format;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Category;
use teacher\modules\v2\models\SendMessage;


class CategoryController extends MainController
{
	const CHOOSE_UNIQUE = 1;
	const CHOOSE_MORE   = 2;

	public $modelClass = 'teacher\modules\v2\models\Category';

	/**
	  *[获取图片所有分类]
	  *
	  *@param string category_id 课件分类
	  *
	  *
	*/
	public function actionGetList()
	{
		$modelClass   = $this->modelClass;
		$category_id  = Admin::findOne($this->user_id)->category_id;
		$list = $modelClass::find()
				 	->select('id,name')
				 	->andFilterWhere(['id' => Format::explodeValue($category_id),'studio_type'=>$this->studio_type])
				 	->andFilterWhere(['type' => $modelClass::TYPE_PICTURE, 'status' => $modelClass::STATUS_ACTIVE])
				 	->andFilterWhere(['level' => 0])
					->orderBy('priority, id')
					->all();
		
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
        return array(
        	  'category_id' => 0,
        	  'name'        => '分类',
        	  'level'       => 1,
        	  'choose_type' => self::CHOOSE_UNIQUE,
        	  'request_param' => 'category_id',
        	  'items'          => $list
        );
	}

	/*	
	 *[获取美术圈图片分组]
	 *
	 *@param parent_id 父级id
	 *
	*/
	public function actionGetPicType($category_id = 0,$level=0)
	{
		$size = Yii::$app->params['oss']['Size']['320x320'];
		switch ($level) {
			case 0:
	            $data = Curl::metis_file_get_contents(
	                Yii::$app->params['metis']['Url']['category'].'?type=3&ids=207,208&parent_id='.$category_id
	            );
	            foreach ($data as $key => $value) {
	            	$data[$key]->category_id = $value->id;
	            	$data[$key]->level = 1;
	            	$data[$key]->image = Oss::getClassImg($key).$size;
	            }
	            $_GET['message'] = Yii::t('teacher','Sucessfully List');
	            return array([
	            	  'category_id' => $category_id,
	            	  'name'        => '分类',
	            	  'level'       => 1,
	            	  'choose_type' => self::CHOOSE_UNIQUE,
	            	  'request_param' => 'category_id',
	            	  'items'          => $data
	            ]);
				break;
			case 1:
	            $data = Curl::metis_file_get_contents(
	                Yii::$app->params['metis']['Url']['category'].'?type=3&ids=207,208&parent_id='.$category_id
	            );
	            foreach ($data as $key => $value) {
	            	$data[$key]->category_id = $value->id;
	            	$data[$key]->level = 2;
	            }
	            $_GET['message'] = Yii::t('teacher','Sucessfully List');

	            return array([
	            	  'category_id' => $category_id,
	            	  'name'        => '分类',
	            	  'level'       => 2,
	            	  'choose_type' => self::CHOOSE_UNIQUE,
	            	  'request_param' => 'category_id',
	            	  'items'          => $data
	            ]);
				break;
			case 2:
		        $keyword = Curl::metis_file_get_contents(
		            Yii::$app->params['metis']['Url']['keyword'].'?category_id='.$category_id.'&type=3'
		        );

		        foreach ($keyword as $key => $value) {
	            	$keyword[$key]->keyword_id = $value->id;
	            	$keyword[$key]->level = 3;
	            }

		        $keywrod_array = array();
		        foreach ($keyword as $key => $value) {
		        	$keywrod_array[$value->id] = substr($value->priority,0,1);
		        }
		    	
		        $sort_keyword = array_unique(array_values($keywrod_array));

		        $sort_array = array();
		        foreach ($sort_keyword as $key => $value) {
		        	foreach ($keywrod_array as $k => $v) {
		        		if($v == $value) {
		        			$sort_array[$key][] = $k;
		        		}
		        	}
		        }
		        $result_keyword = array();
		        foreach ($keyword as $key => $value) {
		        	foreach ($sort_array as $k => $v) {
		        		if(in_array($value->id,$v)) {
		        			$result_keyword[$k][] = $value;
		        		}
		        	}
		        }
		        $_GET['message'] = Yii::t('teacher','Sucessfully List');
		        $keywords = array();
		        foreach ($result_keyword as $key => $value) {
		        	$keywords[] =  array(
		            	  'category_id' => $category_id,
		            	  'name'        => '关键字',
		            	  'level'       => 3,
		            	  'choose_type' => self::CHOOSE_MORE,
		            	  'request_param' => 'keyword_id',
		            	  'items'          => array_values($value)
	            	);
		        }
		        $_GET['message'] = Yii::t('teacher','Sucessfully List');

		        return $keywords;
				break;
			default:
				#return;
				break;
		}
	}

	/*	
	 *[获取美术圈视频分组]
	 *
	 *@param category_id 父级id
	 *
	*/
	public function actionGetVideoType($category_id = 0,$level=0)
	{
		$size = Yii::$app->params['oss']['Size']['320x320'];
		switch ($level) {
			case 0:
	            $data = Curl::metis_file_get_contents(
	                Yii::$app->params['metis']['Url']['category'].'?type=2&ids=194,195,5&parent_id='.$category_id
	            );
	     		foreach ($data as $key => $value) {
	            	$data[$key]->category_id = $value->id;
	            	$data[$key]->level = 1;
	            	$data[$key]->image = Oss::getClassImg($key).$size;
	            }
	            $_GET['message'] = Yii::t('teacher','Sucessfully List');
	            return array([
	            	  'category_id' => $category_id,
	            	  'name'        => '分类',
	            	  'level'       => 1,
	            	  'choose_type' => self::CHOOSE_UNIQUE,
	            	  'request_param' => 'category_id',
	            	  'items'          => $data
	            ]);
				break;
			case 1:
	            $data = Curl::metis_file_get_contents(
	                Yii::$app->params['metis']['Url']['category'].'?type=2&ids=194,195,5&parent_id='.$category_id
	            );
	    		foreach ($data as $key => $value) {
	            	$data[$key]->category_id = $value->id;
	            	$data[$key]->level = 2;
	            }
	            $_GET['message'] = Yii::t('teacher','Sucessfully List');
	            return array([
	            	  'category_id' => $category_id,
	            	  'name'        => '分类',
	            	  'level'       => 2,
	            	  'choose_type' => self::CHOOSE_UNIQUE,
	            	  'request_param' => 'category_id',
	            	  'items'          => $data
	            ]);
				break;
			case 2:
		        $keyword = Curl::metis_file_get_contents(
		            Yii::$app->params['metis']['Url']['keyword'].'?category_id='.$category_id.'&type=2'
		        );
		     	foreach ($keyword as $key => $value) {
	            	$keyword[$key]->keyword_id = $value->id;
	            	$keyword[$key]->level = 3;
	            	
	            }
		        $keywrod_array = array();

		        foreach ($keyword as $key => $value) {
		        	$keywrod_array[$value->id] = substr($value->priority,0,1);
		        }
		    	
		        $sort_keyword = array_unique(array_values($keywrod_array));

		        $sort_array = array();
		        foreach ($sort_keyword as $key => $value) {
		        	foreach ($keywrod_array as $k => $v) {
		        		if($v == $value) {
		        			$sort_array[$key][] = $k;
		        		}
		        	}
		        }
		        $result_keyword = array();
		        foreach ($keyword as $key => $value) {
		        	foreach ($sort_array as $k => $v) {
		        		if(in_array($value->id,$v)) {
		        			$result_keyword[$k][] = $value;
		        		}
		        	}
		        }
		        $_GET['message'] = Yii::t('teacher','Sucessfully List');
		        $keywords = array();
		        foreach ($result_keyword as $key => $value) {
		        	$keywords[] =  array(
		            	  'category_id' => $category_id,
		            	  'name'        => '关键字',
		            	  'level'       => 3,
		            	  'choose_type' => self::CHOOSE_MORE,
		            	  'request_param' => 'keyword_id',
		            	  'items'          => array_values($value)
	            	);
		        }
		        $_GET['message'] = Yii::t('teacher','Sucessfully List');
		        return $keywords;
				break;
			default:
				break;
		}
	}
}
?>