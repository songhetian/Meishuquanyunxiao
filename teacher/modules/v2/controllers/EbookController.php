<?php 
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Curl;
use teacher\modules\v2\models\CodeAdmin;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\EbookCollect;

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
		$_GET['message'] = "获取缓存数据成功";

		$cache = Yii::$app->cache;
		$res = $cache->get('category'.'?parent_id=0&type=5');

		if ($res === false) {
			$res = Curl::metis_file_get_contents(
            	Yii::$app->params['metis']['Url']['category'].'?parent_id=0&type=5'
        	);
        	$cache->set('category'.'?parent_id=0&type=5',$res,120);
        	$_GET['message'] = Yii::t('teacher','Sucessfully List');
		}

		return $res;
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
        $_GET['message'] = "获取缓存成功";
		$cache = Yii::$app->cache;
		$res = $cache->get('ebook_banners'.'?limit='.$limit);

		if ($res === false) {
			$res = Curl::metis_file_get_contents(
	        	Yii::$app->params['metis']['Url']['ebook_banners'].'?limit='.$limit
	        );
	        foreach ($res as $value) {
	        	$value->pic_url_v2 = $value->banner_pic;
	        	$value->size = $this->getSize();
	        }

        	$cache->set('ebook_banners'.'?limit='.$limit, $res, 120);
        	$_GET['message'] = Yii::t('teacher','Sucessfully List');
		}
        
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
	public function actionGetEbooks($category_id = 0, $keyword_id=0, $search_input = '', $limit = 10, $page = 0, $book_type = 0)
	{
		error_reporting(0);

		if($book_type == 1 && $this->user_role == 10) {

			$item_name =  CodeAdmin::findOne($this->user_id)->auths->item_name;

			$pid = substr($item_name,-3);

			if($pid != "001") {
				return SendMessage::sendErrorMsg(Yii::t('teacher','No Auth'));
			}
		}
		if($category_id == 0) {

			$category_id = NULL;
		}

		$_GET['message'] = "获取缓存数据成功";

		$cache = Yii::$app->cache;
		$res = $cache->get('ebook'.'?category='.$category_id.'&keyword='.$keyword_id.'&limit='.$limit.'&search_input='.$search_input.'&page='.$page.'&book_type='.$book_type);

		if ($res === false) {
			$res = Curl::metis_file_get_contents(
	        	Yii::$app->params['metis']['Url']['ebook'].'?category='.$category_id.'&keyword='.$keyword_id.'&limit='.$limit.'&search_input='.$search_input.'&page='.$page.'&book_type='.$book_type
	        );

        	$cache->set('ebook'.'?category='.$category_id.'&keyword='.$keyword_id.'&limit='.$limit.'&search_input='.$search_input.'&page='.$page.'&book_type='.$book_type,$res,120);
        	$_GET['message'] = Yii::t('teacher','Sucessfully List');
		}

		//获取收藏列表
		$EbookCollects =  EbookCollect::getCollects($this->user_id,$this->user_role);
		if($EbookCollects){
	        foreach ($res as $value) {
				$value->size     = $this->getSize();
				if(in_array($value->id, $EbookCollects)){
					$value->is_collect = true;
				}else{
					$value->is_collect = false;
				}
	        }
	    }else{
	        foreach ($res as $value) {
	        	$value->size = $this->getSize();
	        	$value->is_collect = false;
	        }	    	
	    }
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
	public function actionGetMainEbookList($limit = 10, $page = 0  ,$book_type = 0)
	{
		error_reporting(0);

		$cache = Yii::$app->cache;


		$res['latests']['title'] = '最新上传';
		$res['latests']['category'] = 0;
		$res['latests']['ebooks'] = $cache->get('ebook'.'?limit='.$limit.'&page='.$page.'&book_type='.$book_type);
		if ($res['latests']['ebooks'] === false) {
			$res['latests']['ebooks'] = Curl::metis_file_get_contents(
	        	Yii::$app->params['metis']['Url']['ebook'].'?limit='.$limit.'&page='.$page.'&book_type='.$book_type
	        );
	        $cache->set('ebook'.'?limit='.$limit.'&page='.$page.'&book_type='.$book_type,$res['latests']['ebooks'],120);
		}

		//获取收藏列表
		$EbookCollects =  EbookCollect::getCollects($this->user_id,$this->user_role);


        if($EbookCollects){
	        foreach ($res['latests']['ebooks'] as $value) {
				$value->size     = $this->getSize();
				if(in_array($value->id, $EbookCollects)){
					$value->is_collect = true;
				}else{
					$value->is_collect = false;
				}
	        }
	    }else{
	        foreach ($res as $value) {
	        	$value->size = $this->getSize();
	        	$value->is_collect = false;
	        }	    	
	    }
        
	    $categorys = $cache->get('category');

		if ($categorys === false) {
			$categorys = Curl::metis_file_get_contents(
            	Yii::$app->params['metis']['Url']['category'].'?parent_id=0&type=5'
        	);
        	$cache->set('category'.'?parent_id=0&type=5',$categorys,120);
		}

        foreach ($categorys as $value) {
        	$ebooks = [];
        	$ebooks[$value->id]['title'] = $value->name;
        	$ebooks[$value->id]['category'] = $value->id;
        	$ebooks[$value->id]['ebooks'] = $cache->get('ebook'.'?category='.$value->id.'&limit='.$limit.'&page='.$page);

			if ($ebooks[$value->id]['ebooks'] === false) {
				$ebooks[$value->id]['ebooks'] = Curl::metis_file_get_contents(
        			Yii::$app->params['metis']['Url']['ebook'].'?category='.$value->id.'&limit='.$limit.'&page='.$page
        		);
				$cache->set('ebook'.'?category='.$value->id.'&limit='.$limit.'&page='.$page,$ebooks[$value->id]['ebooks'],120);
			}
        	
        	foreach ($ebooks[$value->id]['ebooks'] as $v) {
        		$v->size = $this->getSize();
	    	}
	        if($EbookCollects){
		        foreach ($ebooks[$value->id]['ebooks'] as $v) {
					$v->size     = $this->getSize();
					if(in_array($v->id, $EbookCollects)){
						$v->is_collect = true;
					}else{
						$v->is_collect = false;
					}
		        }
		    }else{
	        	foreach ($ebooks[$value->id]['ebooks'] as $v) {
	        		$v->size = $this->getSize();
	        		$v->is_collect = false;
		    	}	    	
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
	public function actionGetEbookInfo($ebook_id,$page = 0,$limit = 99)
	{
		$res = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['commoditys'].'?page='.$page.'&limit='.$limit.'&e_book_id='.$ebook_id
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
						if(strstr($info_value->preview_image, 'web.meishuquan')){
			                $pathinfo = pathinfo($info_value->preview_image);
			                $thumbs_image =  $pathinfo['dirname'].'/'.basename($info_value->preview_image,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
			            }else{
			                 $thumbs_image = $info_value->preview_image.'?x-oss-process=style/thumb';
			            }
	            		$array = [];
	            		$array['video_id'] = $info_value->id;
	            		$array['title']    = $info_value->title;
	            		$array['charging_option'] = $info_value->charging_option;
	            		$array['cc_id'] = $info_value->chapter;
	            		$array['preview_image'] = $info_value->preview_image;
	            		$array['thumbs_image'] = $thumbs_image;
	            	}
					$value->video_url = $array;
				}
			}else{
				unset($value->video_url);
			}
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return $res;
	}


	

	/**
	 * [getSize description]
	 * @开发者    tianhesong
	 * @创建时间   2020-05-25 T 14:42:33+0800
	 * @return [type]                   [description]
	 */
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
