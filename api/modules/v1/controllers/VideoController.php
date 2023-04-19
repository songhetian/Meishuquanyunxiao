<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\Format;

class VideoController extends MainController
{
    public $modelClass = 'api\modules\v1\models\Video';
	
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
	            'pagesize' => 99,
	        ]
        ]);
    }

        /**
     * [获取美术圈视频banner图]
     *
     *@return mixed(array)
    */
    public function actionGetBanner()
    {
        
        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['metis-banner'].'?banner_type=2'
        );

        $material = [];
        foreach ($courses as $key => $value) {
            $material[] = (object)[
                'course_id' => $value->id,
                'title' => $value->title,
                'preview_image' => $value->pic_url.'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c',
            ];
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $material;
    }

        /**
     * [获取美术圈视频-v2]
     *
     * @param  category_id[一级分类] 
     *        keyword_id[关键字]  publishing_id[出版社] page[当前页] page_limit[显示数量]
     *@return mixed(array)
    */
    public function actionList($category_id='',$keyword_id='',$type='',$limit=15,$page=0)
    {
        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['metis-video'].'?category='.$category_id.'&keyword='.$keyword_id.'&type='.$type.'&limit='.$limit.'&page='.$page
        );

        $material = [];

        foreach ($courses as $key => $value) {
            $material[] = (object)[
                'course_id' => $value->id,
                'title'  => $value->title,
                'number' => $value->chapter_num,
                'studio' => $value->studio->studio_name,
                'preview_image' => $value->preview,
            ];
        }

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $material;
    }

    /**
     * [获取美术圈视频列表详情]
     *
     * @param  course_id 课程id
     *@return  mixed(array)
    */
    public function actionInfo($course_id)
    {

        $chapters = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['course-chapters'].'?course_id='.$course_id
        );

        $infos = [];
        foreach ($chapters as $v) {
            $infos[] = (object)[
                'video_id' => $v->id,
                'title' => $v->title,
                'charging_option' => $v->charging_option,
                'cc_id' => $v->chapter,
                'preview_image' => $v->preview_image.'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c',
                'intro'         => $v->chapter_detail,
                'duration'      => $v->video_length,
                'studio_name'   => $v->studio_id->studio_name,
            ];
        }

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $infos;
    }
}