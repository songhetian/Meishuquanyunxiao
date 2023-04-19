<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\App;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\Family;
use teacher\modules\v2\models\Tencat;
use components\Push;
use components\PostPush;

class AppController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\App';

    public function actionUpdate()
    {
        $studio_id = Yii::$app->request->post('studio_id');
        if(!$studio_id){
            return '缺少参数'; 
        }

        $modelClass = $this->modelClass;

        $_GET['message'] = Yii::t('teacher','Sucessfully List');

        return $modelClass::findOne(['studio_id' => $studio_id, 'status' => $modelClass::STATUS_ACTIVE]);
        
    }
    public function actionTest() {
        $push = new Push('2d53ea1b1081a','cbd3d60b24bbd4670779bc9d698128e7');

        $neirong = array(
            'appkey' => '2d53ea1b1081a',
            'plats'  => [1,2],
            'target' => 1,
            'content' => '我是测试1',
            'type'    => 1,
            'scheme'  => stripslashes('https://api.teacher.meishuquanyunxiao.com/v2/ebook/get-ebook-info'),
            'data'    => array('value'=>594),
           # "extras"    => array('key' => "https://api.teacher.meishuquanyunxiao.com/v2/ebook/get-ebook-info",'value'=>594), 
            "androidContent" => ['http://meishuquan.oss-cn-beijing.aliyuncs.com/ebook/pic_url/0dcc545a280e943fc1e84a39c5bf2274.jpeg?x-oss-process=style/250x250'],
            'androidstyle'  => 2,
            "androidTitle"=>"测试标题"
        );

        return PostPush::PushMsg(1,'1111111',1,"http://meishuquan.oss-cn-beijing.aliyuncs.com/ebook/pic_url/0dcc545a280e943fc1e84a39c5bf2274.jpeg?x-oss-process=style/250x250",2,'11111',"www.baidu.com");
    }
    //根据身份获取图表
    public function actionGetIcon($is_review) {
        $role = $this->user_role;

        $studio_id = $this->studio_id;

        $_GET['message'] = Yii::t('teacher','Sucessfully List');

        if($this->user_role == 10) {
           $status =   (Admin::findOne($this->user_id)->codes) ? true : false;
        }elseif($this->user_role == 20) {
            $status =  (User::findOne($this->user_id)->codes) ? true : false;
        }elseif($this->user_role == 30){
            $status =  (Family::findOne($this->user_id)->relation_id) ? true : false;
        }
        
        return App::GetInco($is_review,$this->user_role,$status,$studio_id);

    }
}