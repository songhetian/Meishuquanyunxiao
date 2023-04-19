<?php 
namespace teacher\modules\v1\controllers;

use Yii;
use common\models\Curl;

class EbookController extends MainController
{

	public $modelClass = 'teacher\modules\v1\models\Ebook';

	/**
	 * [actionGetEbookCategorys 获取电子书分类]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-12-13
	 * @return    [type]        [description]
	 */
	public function actionGetEbookCategorys()
	{
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		return Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['category'].'?parent_id=0&type=5'
        );
	}
	
	/**
	 * [actionGetEbookBanners 获取电子书轮播图]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-12-18
	 * @param     integer       $limit [数量]
	 * @return    [type]               [description]
	 */
	public function actionGetEbookBanners($limit = 5)
	{
		$res = Curl::metis_file_get_contents(
        	Yii::$app->params['metis']['Url']['ebook_banners'].'?limit='.$limit
        );
        foreach ($res as $value) {
        	$value->size = $this->getSize();
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
		return $res;
	}

	/**
	 * [actionGetMetisEbooks 获取电子书]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-12-12
	 * @param     string        $category_id [分类ID]
	 * @param     integer       $limit       [获取数量]
	 * @param     integer       $page        [页码]
	 * @return    [type]                     [description]
	 */
	public function actionGetEbooks($category_id = 0, $search_input = '', $limit = 10, $page = 0)
	{
		$res = Curl::metis_file_get_contents(
        	Yii::$app->params['metis']['Url']['ebook'].'?category='.$category_id.'&search_input='.$search_input.'&limit='.$limit.'&page='.$page;
        );

        foreach ($res as $value) {
        	$value->size = $this->getSize();
        }
		$_GET['message'] = Yii::t('teacher','Sucessfully List');
		return $res;
	}

	/**
	 * [actionGetMainEbookList 获取电子书列表]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-12-19
	 * @param     integer       $limit [获取数量]
	 * @param     integer       $page  [页码]
	 * @return    [type]               [description]
	 */
	public function actionGetMainEbookList($limit = 10, $page = 0)
	{
		$res['latests']['title'] = '最新上传';
		$res['latests']['category'] = 0;
		$res['latests']['ebooks'] = Curl::metis_file_get_contents(
        	Yii::$app->params['metis']['Url']['ebook'].'?limit='.$limit.'&page='.$page
        );
		foreach ($res['latests']['ebooks'] as $value) {
        	$value->size = $this->getSize();
        }
        
        $categorys = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['category'].'?parent_id=0&type=5'
        );
        foreach ($categorys as $value) {
        	$ebooks = [];
        	$ebooks[$value->id]['title'] = $value->name;
        	$ebooks[$value->id]['category'] = $value->id;
        	$ebooks[$value->id]['ebooks'] = Curl::metis_file_get_contents(
        		Yii::$app->params['metis']['Url']['ebook'].'?category='.$value->id.'&limit='.$limit.'&page='.$page
        	);
        	foreach ($ebooks[$value->id]['ebooks'] as $v) {
        		$v->size = $this->getSize();
	    	}
        	$res['categorys'][] = $ebooks[$value->id];
        }
        return $res;
	}

	/**
	 * [actionGetEbookInfo 获取电子书详情]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-12-13
	 * @param     [type]        $ebook_id [电子书ID]
	 * @return    [type]                  [description]
	 */
	public function actionGetEbookInfo($ebook_id)
	{
		$res = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['commoditys'].'?page=0&limit=99&e_book_id='.$ebook_id
        );
        foreach ($res as $value) {
        	$value->size = $this->getSize();
        	if($value->video_url){
        		$value->video_url = str_replace(["\r", "\n"], '', $value->video_url); 
				$int  = substr($value->video_url, strripos($value->video_url, '=') + 1);
				$info  = Curl::metis_file_get_contents(
               		Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$int
            	);
				if($info){
					foreach ($info as $info_key => $info_value) {
	            		$array = [];
	            		$array['video_id'] = $info_value->id;
	            		$array['title']    = $info_value->title;
	            		$array['charging_option'] = $info_value->charging_option;
	            		$array['cc_id'] = $info_value->chapter;
	            		$array['preview_image'] = $info_value->preview_image;
	            	}
					$value->video_url = $array;
				}
			}else{
				unset($value->video_url);
			}
        }
        return $res;
	}

	public function getSize()
	{
		$size = [];
		$sizes = Yii::$app->params['oss']['Size'];
        foreach ($sizes as $k => $v) {
            $size['image_' . $k] = $v;
        }
        return $size;
	}
}
?>