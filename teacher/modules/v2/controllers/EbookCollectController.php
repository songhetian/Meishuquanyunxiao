<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Curl;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\EbookCollect;



class EbookCollectController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\EbookCollect';

    public function actionList($name = '',$category_id = '',$page = 0 ,$limit = 10)
    {
        EbookCollect::$admin = $this->user_id;

        EbookCollect::$role_type = $this->user_role;

        if($category_id == 0 ) {
            $category_id = NULL;
        }

        $offset = $page * $limit;

        $lists = EbookCollect::find() 
                            ->where([
                                    'admin_id' => $this->user_id,
                                    'status'    => 10
                              ])
                            ->andFilterWhere(['category_id' => $category_id])
                            ->andFilterWhere(['like','name',$name])
                            ->orderBy('created_at DESC')
                            ->offset($offset)
                            ->limit($limit)
                            ->all();

        $_GET['message']  = "获取信息成功";

        return  $lists;
    }


    /**
     * [actionCollect 收藏]
     * @开发者    tianhesong
     * @创建时间   2020-05-28T15:30:09+0800
     * @param  [type]                   $ebook_id [电子书id]
     * @return [type]                             [description]
     */
    public function actionCollect($ebook_id) {
        if(EbookCollect::getListById($ebook_id,$this->user_id,$this->user_role)) {
           return SendMessage::sendSuccessMsg("该电子书已收藏");
        }

        $info = EbookCollect::getCancel($ebook_id,$this->user_id,$this->user_role);

        if($info){
            $EbookCollect         = $info;
            $EbookCollect->status = 10;
        }else{
            $EbookCollect  = new EbookCollect();
            $res =    Curl::metis_file_get_contents(
                            Yii::$app->params['metis']['Url']['ebook'].'?search_id='.$ebook_id.'&page=0&limit=1'
                        );

            $Ebook = $res[0];

            $EbookCollect->ebook_id           =  $Ebook->id;
            if($Ebook->category){
                $EbookCollect->category_id       =  $Ebook->category_pid;
            }else{
                $EbookCollect->category_id       =  NULL;
            }
            
            $EbookCollect->admin_id           =  $this->user_id;
            $EbookCollect->role               =  $this->user_role;
            $EbookCollect->studio_id          =  $this->studio_id;
            $EbookCollect->name               =  $Ebook->title;
            $EbookCollect->pic_url            =  $Ebook->pic_url;
            $EbookCollect->publishing_company =  $Ebook->publishing_company;
            $EbookCollect->book_type          =  $Ebook->book_type;
        }

        if($EbookCollect->save()) {
            return SendMessage::sendSuccessMsg("收藏成功");
        }else{
            return SendMessage::sendVerifyErrorMsg1($EbookCollect);
        }
    }

    /**
     * [actionCancel 取消收藏]
     * @开发者    tianhesong
     * @创建时间   2020-05-29T13:21:35+0800
     * @param  [type]                   $ebook_id [description]
     * @return [type]                             [description]
     */
    public function actionCancel($ebook_id) {

        $modelClass = $this->modelClass;

        $model = $modelClass::findOne([
                                'ebook_id' =>  $ebook_id,
                                'admin_id' =>  $this->user_id,
                                'role'     =>  $this->user_role,
                                'status'   =>  10
                            ]);
        if(!$model) {
            return SendMessage::sendErrorMsg("已取消");
        }
        $model->status = 0;

        if($model->save()) {
            return SendMessage::sendSuccessMsg("取消成功");
        }else{
            return SendMessage::sendVerifyErrorMsg1($model);
        }

    }
    /**
     * [actionSchool 校园图书馆]
     * @开发者    tianhesong
     * @创建时间   2020-05-29T14:09:20+0800
     * @return [type]                   [description]
     */
    public function actionSchool($name = '',$category_id = '' ,$page = 0 ,$limit = 10) {

        EbookCollect::$admin     = $this->user_id;
        EbookCollect::$role_type = $this->user_role;
        if($category_id == 0 ) {
            $category_id = NULL;
        }
        $offset = $page * $limit;
        $lists = EbookCollect::find() 
                            ->where([
                                    'studio_id' => $this->studio_id,
                                    'is_public' => 10,
                                    'status'    => 10
                              ])
                            ->andFilterWhere(['category_id' => $category_id])
                            ->andFilterWhere(['like','name',$name])
                            ->offset($offset)
                            ->limit($limit)
                            ->orderBy('created_at DESC')
                            ->all();

        $_GET['message']  = "获取信息成功";

        return  $lists;

    }

    /**
     * [actionShare 分享到校园素材库]
     * @开发者    tianhesong
     * @创建时间   2020-06-01T12:56:15+0800
     * @param  [type]                   $ebook_id [description]
     * @return [type]                             [description]
     */
    public function actionShare($ebook_id) {

        $modelClass = $this->modelClass;

        $model = $modelClass::findOne([
                                'ebook_id' =>  $ebook_id,
                                'admin_id' =>  $this->user_id,
                                'role'     =>  $this->user_role,
                                'status'   =>  10
                            ]);
        if(!$model) {
             return SendMessage::sendErrorMsg("不存在");
        }
        $model->is_public = 10;

        if($model->save()) {
            return SendMessage::sendSuccessMsg("分享成功");
        }else{
            return SendMessage::sendVerifyErrorMsg1($model);
        }

    }

    /**
     * [actionCancelShare 取消收藏]
     * @开发者    tianhesong
     * @创建时间   2020-06-01T12:57:27+0800
     * @param  [type]                   $ebook_id [description]
     * @return [type]                             [description]
     */
    public function actionCancelShare($ebook_id) {

        $modelClass = $this->modelClass;

        $model = $modelClass::findOne([
                                'ebook_id' =>  $ebook_id,
                                'admin_id' =>  $this->user_id,
                                'role'     =>  $this->user_role,
                                'status'   =>  10
                            ]);
        if(!$model) {
             return SendMessage::sendErrorMsg("不存在");
        }
        $model->is_public = 0;

        if($model->save()) {
            return SendMessage::sendSuccessMsg("取消成功");
        }else{
            return SendMessage::sendVerifyErrorMsg1($model);
        }

    }
    /**
     * [actionGetCategory 获取分类]
     * @开发者    tianhesong
     * @创建时间   2020-05-28T15:28:38+0800
     * @return [type]                   [description]
     */
    public function actionGetCategory() {
        $categorys = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['category'].'?parent_id=0&type=5'
        );

        $list = array();

        foreach ($categorys as $key => $value) {
            $list[$key]['name']        =  $value->name;
            $list[$key]['title']       =  $value->name;
            $list[$key]['category_id'] =  $value->category_id;
            $list[$key]['category']    =  $value->category_id;
        }
        $first = array(
                     'name'        => '全部',
                     'title'       => '全部',
                     'category_id' => 0,
                     'category'    => 0

        );
        array_unshift($list, $first);
        $_GET['message']  = "获取信息成功";

        return $list;
    }
}