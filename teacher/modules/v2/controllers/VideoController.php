<?php
namespace teacher\modules\v2\controllers;

use Yii;
use teacher\modules\v2\models\Admin;
use components\Spark;
use common\models\Curl;
use common\models\Format;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use common\models\GoodsAlipayOrder;
use common\models\GoodsWechatOrder;
use common\models\GoodsIapOrder;
use teacher\modules\v2\models\Picture;
use teacher\modules\v2\models\Video;
use teacher\modules\v1\models\Errors;
use teacher\modules\v1\models\Group;
use teacher\modules\v2\models\SouceGroup;
use teacher\modules\v2\models\SendMessage;


class VideoController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Video';
	
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
     *[获取教案中分组图片]
     *
     *@param material_library_id 资源id
     *
     *
    */

    public function actionGetVideo($group_id,$page=0,$limit=20)
    {
        $modelClass = $this->modelClass;

        $ids = Format::explodeValue(Group::findOne($group_id)->material_library_id);

        $offset   = $page*$limit;

        $videos = $modelClass::find()
                                 ->where(['id' => $ids, 'status' => $modelClass::STATUS_ACTIVE])
                                 ->offset($offset)
                                 ->limit($limit)
                                 ->all();

        //添加查看次数
        $modelClass::updateAllCounters(['watch_count' => 1], ['id' => $ids]);

        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');

        return $videos;
    }
    /**
     * [获取美术圈视频]
     *
     * @param  category_id[一级分类] 
     *        keyword_id[关键字]  publishing_id[出版社] page[当前页] page_limit[显示数量]
     *@return mixed(array)
    */
    public function actionGetmeisList($category_id='',$keyword_id='',$publishing_id='',$limit=15,$page=0,$charging_option=1)
    {
        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['course'].'?category='.$category_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id.'&limit='.$limit.'&page='.$page.'&charging_option='.$charging_option
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
            Yii::$app->params['metis']['Url']['metis-banner'].'?banner_type=2&charging_option=1'
        );
        $material = [];

        if($this->studio_id == 271 && $this->user_role != 10){
            $_GET['message'] = Yii::t('teacher','Sucessfully List');
            return  $material;
        }

        foreach ($courses as $key => $value) {
            if(strstr($value->pic_url, 'web.meishuquan')){
                $pathinfo = pathinfo($value->pic_url);
                $thumbs_image =  $pathinfo['dirname'].'/'.basename($value->pic_url,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
            }else{
                 $thumbs_image = $value->pic_url.'?x-oss-process=style/950x540';
            }
            $material[] = (object)[
                'course_id' => $value->id,
                'title' => $value->title, 
                'preview_image' => $value->pic_url,
                'thumbs_image' => $thumbs_image,
            ];
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $material;
    }


    /**
     * [获取美术圈视频banner图（含收费）]
     *
     *@return mixed(array)
    */
    public function actionGetBannerV2()
    {
        
        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['metis-banner'].'?banner_type=2'
        );

        $material = [];

        if($this->studio_id == 271 && $this->user_role != 10){
            $_GET['message'] = Yii::t('teacher','Sucessfully List');
            return  $material;
        }
        
        foreach ($courses as $key => $value) {
            if(strstr($value->pic_url, 'web.meishuquan')){
                $pathinfo = pathinfo($value->pic_url);
                $thumbs_image =  $pathinfo['dirname'].'/'.basename($value->pic_url,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
            }else{
                 $thumbs_image = $value->pic_url.'?x-oss-process=style/950x540';
            }
            $material[] = (object)[
                'course_id' => $value->id,
                'title' => $value->title, 
                'preview_image' => $value->pic_url,
                'thumbs_image' => $thumbs_image,
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
    public function actionList($category_id='',$keyword_id='',$type='',$limit=15,$page=0,$charging_option=1,$search_input = "")
    {
        $material = [];

        if($this->studio_id == 271 && $this->user_role != 10){
            $_GET['message'] = Yii::t('teacher','Sucessfully List');
            return  $material;
        }

        $courses = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['metis-video'].'?category='.$category_id.'&keyword='.$keyword_id.'&type='.$type.'&limit='.$limit.'&page='.$page.'&charging_option='.$charging_option.'&search_input='.$search_input
        );
       
        foreach ($courses as $key => $value) {
            if(strstr($value->preview, 'web.meishuquan')){
                $pathinfo = pathinfo($value->preview);
                $thumbs_image =  $pathinfo['dirname'].'/'.basename($value->preview,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
            }else{
                 $thumbs_image = $value->preview.'?x-oss-process=style/fix_width';
            }
            $is_pay = false;
            $admin = Admin::findOne(['phone_number'=>$value->phone_number,'studio_id'=>183]);
            if($value->price > 0){
                $is_pay = GoodsAlipayOrder::findOne(['user_id'=>$this->user_id,'user_type'=>$this->user_type,'status'=>1,'goods_type'=>'course','goods_id'=>$value->id]);
                        if(empty($is_pay)){
                            $is_pay = GoodsWechatOrder::findOne(['user_id'=>$this->user_id,'user_type'=>$this->user_type,'status'=>1,'goods_type'=>'course','product_id'=>$value->id]);
                        }
                        if(empty($is_pay)){
                            $is_pay = GoodsIapOrder::findOne(['user_id'=>$this->user_id,'user_type'=>$this->user_type,'goods_type'=>'course','goods_id'=>$value->id]);
                        }
            }else{
                $is_pay = true;
            }

            $is_new = false;
            if ($value->publish_time > (time()- (60*60*24*7))) {
                $is_new = true;
            }
            $admin = Admin::findOne(['phone_number'=>$value->phone_number,'studio_id'=>183]);

            $host_info = 'http://web.meishuquan.net/uploads/ueditor/';
            $desc = str_replace('/uploads/ueditor/',"$host_info",$value->desc);

            $pay_num = 0;
            $pay_num += GoodsAlipayOrder::find()->where(['status'=>1,'goods_type'=>'course','goods_id'=>$value->id])->count('id');
            $pay_num += GoodsWechatOrder::find()->where(['status'=>1,'goods_type'=>'course','product_id'=>$value->id])->count('id');
            $pay_num += GoodsIapOrder::find()->where(['goods_type'=>'course','goods_id'=>$value->id])->count('id');


            $baifenbi = (time()-strtotime($value->create_time))/864000;

            if($baifenbi >= 1){
                $pay_num_temp = $value->fictitious_pay_num;
            }else{
                $pay_num_temp = intval($value->fictitious_pay_num * $baifenbi);
            }


            if($value->id != 782){
                 $material[] = (object)[
                'course_id' => $value->id,
                'price' =>$value->price,
                'title'  => $value->title,
                'number' => $value->chapter_num,
                'studio' => $value->studio->studio_name,
                'preview_image' => $value->preview,
                'thumbs_image' => $thumbs_image,
                'is_pay' =>  $is_pay?true:false,
                'admin_id' =>$admin,
                'teacher_id' =>1,
                'desc' => $desc,
                'pay_desc' =>Yii::$app->params['metis']['pay_desc'][$value->goods_pay_type],
                'pay_num' => $pay_num_temp + $pay_num,
                'is_new' => $is_new,
                'liang' => $baifenbi,
                 ];
            }else{
                if($is_pay){
                     $material[] = (object)[
                    'course_id' => $value->id,
                    'price' =>$value->price,
                    'title'  => $value->title,
                    'number' => $value->chapter_num,
                    'studio' => $value->studio->studio_name,
                    'preview_image' => $value->preview,
                    'thumbs_image' => $thumbs_image,
                    'is_pay' =>  $is_pay?true:false,
                    'admin_id' =>$admin,
                    'teacher_id' =>1,
                    'desc' => $desc,
                    'pay_desc' =>Yii::$app->params['metis']['pay_desc'][$value->goods_pay_type],
                    'pay_num' => $pay_num_temp + $pay_num,
                    'is_new' => $is_new,
                    'liang' => $baifenbi,
                     ];
                }
            }

           
        }
        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $material;
    }


    /**
     * [获取单个美术圈视频]
     *
     * @param  category_id[一级分类] 
     *        keyword_id[关键字]  publishing_id[出版社] page[当前页] page_limit[显示数量]
     *@return mixed(array)
    */
    public function actionCourseOne($course_id,$user_id=0,$user_type='a')
    {

        $is_pay_course = GoodsAlipayOrder::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'status'=>1,'goods_type'=>'course','goods_id'=>$course_id]);
        if(empty($is_pay_course)){
            $is_pay_course = GoodsWechatOrder::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'status'=>1,'goods_type'=>'course','product_id'=>$course_id]);
        }
        if(empty($is_pay_course)){
            $is_pay_course = GoodsIapOrder::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'goods_type'=>'course','goods_id'=>$course_id]);
        }
        $course = Curl::metis_file_get_contents(
            'www.meishuquan.net/rest/course-v2/get-course?course_id='.$course_id
        );
        $admin = Admin::findOne(['phone_number'=>$course[0]->phone_number,'studio_id'=>183]);
        //var_dump($course[0]);

        $chapters = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['course-chapters'].'?course_id='.$course_id
        );

        $infos = [];
        foreach ($chapters as $v) {
            if(strstr($v->preview_image, 'web.meishuquan')){
                $pathinfo = pathinfo($v->preview_image);
                $thumbs_image =  $pathinfo['dirname'].'/'.basename($v->preview_image,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
            }else{
                 $thumbs_image = $v->preview_image.'?x-oss-process=style/thumb';
            }
             $is_pay = GoodsAlipayOrder::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'status'=>1,'goods_type'=>'course_chapter','goods_id'=>$v->id]);
            if(empty($is_pay)){
                $is_pay = GoodsWechatOrder::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'status'=>1,'goods_type'=>'course_chapter','product_id'=>$v->id]);
            }
            if(empty($is_pay)){
                $is_pay = GoodsIapOrder::findOne(['user_id'=>$user_id,'user_type'=>$user_type,'goods_type'=>'course-chapters','goods_id'=>$course_id]);
            }
            $infos[] = (object)[
                'video_id' => $v->id,
                'title' => $v->title,
                'charging_option' => $v->charging_option,
                'cc_id' => $v->chapter,
                'preview_image' => $v->preview_image.'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c',
                'thumbs_image' => $thumbs_image,
                'intro'         => $v->chapter_detail,
                'duration'      => $v->video_length,
                'studio_name'   => $v->studio_id->studio_name,
                'is_pay'        => $is_pay_course?true:($is_pay?true:false),
                'code'          => '',
                'pay_num' =>1,
            ];
        }


        if(strstr($course[0]->preview, 'web.meishuquan')){
            $pathinfo = pathinfo($course[0]->preview);
            $thumbs_image =  $pathinfo['dirname'].'/'.basename($course[0]->preview,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
        }else{
             $thumbs_image = $course[0]->preview.'?x-oss-process=style/thumb';
        }
                        $host_info = 'http://web.meishuquan.net/uploads/ueditor/';
                $desc = str_replace('/uploads/ueditor/',"$host_info",$course[0]->desc);
        $material = (object)[
            'course_id' => $course[0]->id,
            'price' =>$course[0]->price,
            'title'  => $course[0]->title,
            'number' => $course[0]->chapter_num,
            'studio' => $course[0]->studio->studio_name,
            'preview_image' => $course[0]->preview,
            'thumbs_image' => $thumbs_image,
            'is_pay' => $is_pay_course?true:false,
            'admin_id' => $admin,
            'teacher_id' =>1,
            'code'      => 'com.meishuquanyunxiao.artworld.course'.(int)$course[0]->price,
            'chapters_list' => $infos,
            'desc' => $desc,
            'pay_desc' =>Yii::$app->params['metis']['pay_desc'][$course[0]->goods_pay_type],
            'pay_num' => floor($course[0]->id/10)+floor($course[0]->price)+($value->id % 10),
        ];
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
            if(strstr($v->preview_image, 'web.meishuquan')){
                $pathinfo = pathinfo($v->preview_image);
                $thumbs_image =  $pathinfo['dirname'].'/'.basename($v->preview_image,".".$pathinfo['extension']).'_thumb.'.$pathinfo['extension'];
            }else{
                 $thumbs_image = $v->preview_image.'?x-oss-process=style/thumb';
            }
            $infos[] = (object)[
                'video_id' => $v->id,
                'title' => $v->title,
                'charging_option' => $v->charging_option,
                'cc_id' => $v->chapter,
                'preview_image' => $v->preview_image.'?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c',
                'thumbs_image' => $thumbs_image,
                'intro'         => $v->chapter_detail,
                'duration'      => $v->video_length,
                'studio_name'   => $v->studio_id->studio_name,
            ];
        }

        $_GET['message'] = Yii::t('teacher','Sucessfully List');
        return  $infos;
    }

    public function actionCollection($img_url,$admin_id) {

        $picture = new Picture();

        $picture->source = Picture::SOURCE_METIS;

        $picture->image = str_replace('?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c', '', $img_url);

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
     *@param image_id inter 美术圈视频id
     *
    */
    public function actionAddMeis($video_id,$admin_id)
    {
        error_reporting(0);

        $modelClass = $this->modelClass;

        $connect = Yii::$app->db->beginTransaction();

        $group_video = array();

        $VideoList = Format::explodeValue($video_id);
        try {
            //判断数据库中有没有该视频
            foreach ($VideoList as $key => $value) {

                $model = $modelClass::findOne(['status'=>10,'source'=>Video::SOURCE_METIS,'metis_material_id'=>$value]);
                if(!$model){
                    $model = new Video();

                    $data = current(Curl::metis_file_get_contents(
                        Yii::$app->params['metis']['Url']['course-chapter'].'?course_chapter_id='.$value
                    ));

                    $model  =  Video::AddValue($model,$data,$admin_id);

                    if(!$model->save()){
                        throw new ErrorException(Errors::getInfo($model->getErrors()));
                    }
                }

                $group_video[] =  $model->id;
            }

            $group_id    = SouceGroup::getDefault($admin_id,SouceGroup::TYPE_VIDEO,$this->user_role);

            if(!isset($group_id)) {
                $group_id = SouceGroup::CreateDefaut($admin_id,SouceGroup::TYPE_VIDEO,$this->user_role);
            }

            $group = SouceGroup::findOne($group_id);

            if($group->material_library_id){
                $videos = Format::implodeValue(array_unique(array_merge($group_video,Format::explodeValue($group->material_library_id))));
                $group->material_library_id = $videos;
            }else{
                $group->material_library_id = Format::implodeValue($group_video);
            }

            if(!$group->save()){
                #throw new ErrorException(Errors::getInfo($group->getErrors()));
            }
            $connect->commit();
            return SendMessage::sendSuccessMsg(Yii::t('teacher','Collection Success'));
            } catch (ErrorException $e) {
            $connect->rollBack();
            return $e->getMessage();
            return SendMessage::sendErrorMsg("收藏失败!");
        }
    }
}