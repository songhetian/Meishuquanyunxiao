<?php
namespace teacher\modules\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\Format;
use common\models\Curl;
use components\Spark;
use teacher\modules\v1\models\Video;
use teacher\modules\v1\models\SouceGroup;
use teacher\modules\v1\models\SendMessage;
use teacher\modules\v1\models\Picture;

class VideoController extends MainController
{
    public $modelClass = 'teacher\modules\v1\models\Video';
	
	public function actionIndex($material_library_id)
    {
        $modelClass = $this->modelClass;

        $ids = Format::explodeValue($material_library_id);

        $query = $modelClass::find()->where(['id' => $ids, 'status' => $modelClass::STATUS_ACTIVE]);

        //添加查看次数
        $modelClass::updateAllCounters(['watch_count' => 1], ['id' => $ids]);

        $_GET['message'] = Yii::t('teacher', 'Sucessfully Get List');
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
	            'pagesize' => 99,
	        ]
        ]);
    }

    /**
     * [获取美术圈视频]
     *
     * @param  category_id[一级分类] 
     *        keyword_id[关键字]  publishing_id[出版社] page[当前页] page_limit[显示数量]
     *@return mixed(array)
    */
    public function actionGetmeisList($category_id='',$keyword_id='',$publishing_id='',$limit=15,$page=0)
    {
        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['course'].'?category='.$category_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id.'&limit='.$limit.'&page='.$page
        );
        $material = [];
        foreach ($courses as $value) {
            $chapter = [];
            $chapters = Curl::metis_file_get_contents(
                Yii::$app->params['metis']['Url']['course-chapters'].'?course_id='.$value->id
            );
            foreach ($chapters as $v) {
                $chapter[] = (object)[
                    'video_id' => $v->id,
                    'title' => $v->title,
                    'charging_option' => $v->charging_option,
                    'cc_id' => $v->chapter,
                    'preview_image' => $v->preview_image,
                    'duration'      => $v->video_length,
                    'studio_name'   => $v->studio_id->studio_name,
                ];
            }
            $material[] = (object)[
                'course_id' => $value->id,
                'title' => $value->title,
                'chapter' => (object)$chapter
            ];
        }

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $material;
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
    public function actionList($category_id='',$keyword_id='',$search_input='',$type='',$limit=15,$page=0)
    {
        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['metis-video'].'?category='.$category_id.'&keyword='.$keyword_id.'&search_input='.$search_input.'&type='.$type.'&limit='.$limit.'&page='.$page
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

    //
    public function actionCollection($img_url,$admin_id) {

        $picture = new Picture();

        $picture->source = Picture::SOURCE_METIS;

        $picture->image = $img_url;

        $picture->category_id = 0;

        $picture->admin_id  = $admin_id;

        if($picture->save()) {

            $group = SouceGroup::findOne(SouceGroup::getDefault($admin_id,SouceGroup::TYPE_PICTURE,$this->user_role));

            if(!$group) {
               $souce_id = SouceGroup::CreateDefaut($admin_id,SouceGroup::TYPE_PICTURE,$this->user_role);

               $group = SouceGroup::findOne($souce_id);
            }
            if($group->material_library_id){
                $group->material_library_id = $group->material_library_id.','.$picture->id;
            }else{
                $group->material_library_id .= $picture->id;
            }

            $group->save();

            return SendMessage::sendSuccessMsg(Yii::t('teacher', 'Collection Success'));
        }else{
            
             return SendMessage::sendErrorMsg(Yii::t('teacher', 'Handle Field'));
        }

    }

    /*
     *[美术圈视频添加到我的素材库]
     *@param image_id inter 美术圈图片id
     *
    */
    public function actionAddMeis($video_id,$admin_id)
    {
        error_reporting(0);
        $videos = Format::explodeValue($video_id);
        $model = new Video();

        $group = SouceGroup::findOne($group_id);

        $connect = Yii::$app->db->beginTransaction();
        try {
            foreach ($videos as $key => $value) {
                $data = current(Curl::metis_file_get_contents(
                    Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$value
                ));
                $video  =  clone $model;
                $video  =  Video::AddValue($video,$data,$admin_id);
                $video->save();
                $group_video[] = Yii::$app->db->getLastInsertId();
            }

            $group_id    = SouceGroup::getDefault($admin_id,SouceGroup::TYPE_VIDEO);
            
            if(!isset($group_id)) {
                $group_id = SouceGroup::CreateDefaut($admin_id,SouceGroup::TYPE_VIDEO);
            }
            $group = SouceGroup::findOne($group_id);
            if($group->material_library_id){
                $group->material_library_id = $group->material_library_id.','.Format::implodeValue($group_video);
            }else{
                $group->material_library_id = Format::implodeValue($group_video);
            }

            $group->save();
            $connect->commit();
            $list = Video::findAll(['id'=>$group_video]);
            $_GET['message'] = Yii::t('teacher','Collection Success');
            return $list;
            } catch (Exception $e) {
            $connect->rollBack();
            return SendMessage::sendErrorMsg(Yii::t('teacher', 'Verify Error'));
        }
    }
}