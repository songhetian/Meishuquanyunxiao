<?php
namespace frontend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\Campus;
use common\models\News;
use common\models\PrizeList;
use common\models\TeacherList;
use common\models\WorksList;
use common\models\NewList;
use common\models\Prointroduction;
use common\models\EnrollmentGuideList;
use common\models\SchoolPic;
use common\models\Daily;
use common\models\CcLive;
use common\models\Live;
use common\models\DailyComment;
use common\models\UserFollow;
use common\models\App;
use common\models\Studio;
use common\models\User;
use common\models\Family;
use common\models\UserLike;
use common\models\ActivationCode;
use components\Oss;
use teacher\modules\v1\models\Admin;
use yii\filters\AccessControl;

class ShareController extends \yii\web\Controller
{
    //头条分享页面
    public function actionNewListView($new_list_id){
        $new_list = NewList::findOne($new_list_id);
        $host_info = '"http://backend.meishuquanyunxiao.com/assets/upload/';
        $new_list->desc = str_replace('"/assets/upload/',"$host_info",$new_list->desc);
        $new_list->desc = htmlspecialchars_decode($new_list->desc);
        $studio = Studio::findOne(['id'=>$new_list->studio_id]);
        $logo = App::findOne(['studio_id'=>$new_list->studio_id]);
        return $this->renderPartial(
            'new-list-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'new_list' => $new_list,
                'source' => $this->isMobile(),
            ]
        );
    }
    //头条分享页面
    public function actionProintroductionView($prointroduction_id){
        $prointroduction = Prointroduction::findOne($prointroduction_id);
        $host_info = '"http://backend.meishuquanyunxiao.com/assets/upload/';
        $prointroduction->desc = str_replace('"/assets/upload/',"$host_info",$prointroduction->desc);
        $prointroduction->desc = htmlspecialchars_decode($prointroduction->desc);
        $studio = Studio::findOne(['id'=>$prointroduction->studio_id]);
        $logo = App::findOne(['studio_id'=>$prointroduction->studio_id]);
        return $this->renderPartial(
            'prointroduction-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'prointroduction' => $prointroduction,
                'source' => $this->isMobile(),
            ]
        );
    }
    //外部链接
    public function actionWebView($id,$studio_id,$type){
        $studio = Studio::findOne(['id'=>$studio_id]);
        $logo = App::findOne(['studio_id'=>$studio_id]);
        switch ($type) {
            case 'enrollment_guide_list':
                $data = EnrollmentGuideList::findOne($id);
                if(empty($data->url)){
                    return $this->redirect('http://www.meishuquanyunxiao.com/share/zhaosheng-list-view.html?enrollment_guide_list_id='.$data->enrollment_guide_list_id.'&is_banner=1');
                }
                break;
            case 'prize_list':
                $data = PrizeList::findOne($id);
                if(empty($data->url)){
                    return $this->redirect('http://www.meishuquanyunxiao.com/share/prize-list-view.html?prize_list_id='.$data->prize_list_id.'&is_banner=1');
                }
                break;
            case 'new_list':
               $data = NewList::findOne($id);
               if(empty($data->url)){
                    return $this->redirect('http://www.meishuquanyunxiao.com/share/new-list-view.html?new_list_id='.$data->new_list_id.'&is_banner=1');
                }
                break;
            default:
                $data = array();
                break;
        }
        if (empty($data->url)) {
            return $this->renderPartial(
                'web-view',
                [
                    'url' => @$data->url,
                    'logo' => $logo,
                    'studio' => $studio,
                    'source' => $this->isMobile(),
                ]
            );
        }
        return $this->redirect(@$data->url);
    }

    //教师分享页面
    public function actionTeacherListView($teacher_list_id){
        $teacher_list = TeacherList::findOne($teacher_list_id);
        $image =  OSS::getUrl($teacher_list->studio_id,'teacher','pic_url',$teacher_list->pic_url).Yii::$app->params['oss']['Size']['original'];
        $host_info = '"http://backend.meishuquanyunxiao.com/assets/upload/image/';
        $teacher_list->desc = str_replace('"/assets/upload/image/',"$host_info",$teacher_list->desc);
        $teacher_list->desc = htmlspecialchars_decode($teacher_list->desc);
        $studio = Studio::findOne(['id'=>$teacher_list->studio_id]);
        $logo = App::findOne(['studio_id'=>$teacher_list->studio_id]);
        return $this->renderPartial(
            'teacher-list-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'teacher_list' => $teacher_list,
                'image' => $image,
                'source' => $this->isMobile(),
            ]
        );
    }

    //辉煌成绩分享页面
    public function actionPrizeListView($prize_list_id){
        $prize_list = PrizeList::findOne($prize_list_id);
        $studio = Studio::findOne(['id'=>$prize_list->studio_id]);
        $logo = App::findOne(['studio_id'=>$prize_list->studio_id]);
        $host_info = '"http://backend.meishuquanyunxiao.com/assets/upload/image/';
        $prize_list->desc = str_replace('"/assets/upload/image/',"$host_info",$prize_list->desc);
        $prize_list->desc = htmlspecialchars_decode($prize_list->desc);
        return $this->renderPartial(
            'prize-list-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'prize_list' => $prize_list,
                'source' => $this->isMobile(),
            ]
        );
    }

    //招生简章分享页面
    public function actionZhaoshengListView($enrollment_guide_list_id){
        $enrollment_guide_list = EnrollmentGuideList::findOne($enrollment_guide_list_id);
        $studio = Studio::findOne(['id'=>$enrollment_guide_list->studio_id]);
        $logo = App::findOne(['studio_id'=>$enrollment_guide_list->studio_id]);
        $host_info = '"http://backend.meishuquanyunxiao.com/assets/upload/image/';
        $enrollment_guide_list->desc = str_replace('"/assets/upload/image/',"$host_info",$enrollment_guide_list->desc);
        $enrollment_guide_list->desc = htmlspecialchars_decode($enrollment_guide_list->desc);
        return $this->renderPartial(
            'enrollment-list-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'enrollment_guide_list' => $enrollment_guide_list,
                'source' => $this->isMobile(),
            ]
        );
    }
    
    //动态分享页面
    public function actionDailyView($daily_id){
        $daily = Daily::findOne($daily_id);
        $studio = Studio::findOne(['id'=>$daily->studio_id]);
        $logo = App::findOne(['studio_id'=>$daily->studio_id]);
        return $this->renderPartial(
            'daily-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'daily' => $daily,
                'source' => $this->isMobile(),
            ]
        );
    }
    //直播分享页面
    public function actionLiveView($cc_id,$live_id=NULL){
        if(!empty($live_id)){
            $live = Live::findOne($live_id);
            $studio = Studio::findOne(['id'=>$live->studio_id]);
            $logo = App::findOne(['studio_id'=>$live->studio_id]);
        }else{
            $live = Live::find()->where('status = 1 AND cc_id="'.$cc_id.'"')->orderBy('start_time DESC')->limit(1)->all();
            if(!empty($live)){
                $live = $live[0];
                $studio = Studio::findOne(['id'=>$live->studio_id]);
                $logo = App::findOne(['studio_id'=>$live->studio_id]);
                $live->pic_url = OSS::getUrl($live->studio_id,'picture','image',$live->pic_url).Yii::$app->params['oss']['Size']['original'];
            }else{
                $live = CCLive::findOne(['cc_id'=>$cc_id]);
                $studio = Studio::findOne(['id'=>$live->studio_id]);
                $logo = App::findOne(['studio_id'=>$live->studio_id]);
            }
        }
        if(!empty($live->end_time)){
            return $this->renderPartial(
                'live-back',
                [
                    'logo' => $logo,
                    'studio' => $studio,
                    'live' => $live,
                    'source' => $this->isMobile(),
                ]
            );
        }else{
            return $this->renderPartial(
            'live-view',
            [
                'logo' => $logo,
                'studio' => $studio,
                'live' => $live,
                'source' => $this->isMobile(),
            ]
        );
        }
    }

    //true 为手机端 false为网页端
    public function isMobile(){  
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock = preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';
        function CheckSubstrs($substrs,$text){
            foreach($substrs as $substr)
                if(false !== strpos($text,$substr)){
                    return true;
                }
                return false;
        }
        $mobile_os_list = array(
            /*
            'Google Wireless Transcoder',
            'Windows CE', 
            'WindowsCE',
            'Symbian',
            'Android',
            'armv6l',
            'armv5',
            'Mobile',
            'CentOS',
            'mowser',
            'AvantGo',
            'Opera Mobi',
            'J2ME/MIDP',
            'Smartphone',
            'Go.Web',
            'Palm',
            'iPAQ'
            */
        );  
        $mobile_token_list = array(
            /*
            'Profile/MIDP',
            'Configuration/CLDC-',
            '160×160',
            '176×220',
            '240×240',
            '240×320',
            '320×240',
            'UP.Browser',
            'UP.Link',
            'SymbianOS',
            'PalmOS',
            'PocketPC',
            'SonyEricsson',
            'Nokia',
            'BlackBerry',
            'Vodafone',
            'BenQ',
            'Novarra-Vision',
            'Iris',
            'NetFront',
            'HTC_',
            'Xda_',
            'SAMSUNG-SGH',
            'Wapaka',
            'DoCoMo',
            'iPhone',
            'iPod'
            */
            'MeishuquanMessenger',
        );    
                    
        $found_mobile = CheckSubstrs($mobile_os_list,$useragent_commentsblock) || CheckSubstrs($mobile_token_list,$useragent);
        if ($found_mobile){
            return true;
        }else{
            return false;
        }
    }  
}