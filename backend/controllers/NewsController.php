<?php

namespace backend\controllers;

use Yii;
use common\models\News;
use backend\models\NewsSearch;
use yii\helpers\Html;
use common\models\Campus;
use backend\models\Admin;
use components\Oss;
use common\models\PrizeList;
use backend\models\PrizeListSearch;
use common\models\TeacherList;
use backend\models\TeacherListSearch;
use common\models\WorksList;
use common\models\Registration;
use backend\models\RegistrationSearch;
use common\models\RegistrationUser;
use backend\models\RegistrationUserSearch;
use backend\models\WorksListSearch;
use common\models\EnrollmentGuideList;
use backend\models\EnrollmentGuideListSearch;
use common\models\NewList;
use backend\models\NewListSearch;
use common\models\Prointroduction;
use backend\models\ProintroductionSearch;
use common\models\SchoolPic;
use backend\models\SchoolPicSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Format;

/**
 * NewsController implements the CRUD actions for News model.
 */
class NewsController extends Controller
{

    //---------------------------------------学校信息------------------------------------------------//

    public function actionRegistration(){
        $searchModel = new RegistrationUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('registration/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //修改
    public function actionRegistrationInfo(){
        $admin_id = Yii::$app->user->identity->id;
        $model = Registration::findOne(['studio_id'=>Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id]);

        if(!empty($model)){
            if (!empty(Yii::$app->request->post())) {
                $model->name = Yii::$app->request->post('Registration')['name'];
                $model->address = Yii::$app->request->post('Registration')['address'];
                $model->phone_number = Yii::$app->request->post('Registration')['phone_number'];
                $model->studio_id = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
                $latLng=explode(',',Yii::$app->request->post('latLng')); 
                $model->lat = $latLng[0];
                $model->lng = $latLng[1];
                $images = Oss::uploads($model, $model->studio_id, 'registration', 'pic');
                if(!empty($images)){
                    $model->pic = $images[0];
                }
                $model->save();
                return $this->redirect(['registration-info-view', 'studio_id' => Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id]);
            }else{
                return $this->render('registration/create', [
                    'model' => $model,
                ]);
            }
        }else{
            $model = new Registration();
            if (!empty(Yii::$app->request->post())) {
                $model->name = Yii::$app->request->post('Registration')['name'];
                $model->address = Yii::$app->request->post('Registration')['address'];
                $model->phone_number = Yii::$app->request->post('Registration')['phone_number'];
                $model->studio_id = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
                $latLng=explode(',',Yii::$app->request->post('latLng')); 
                $model->lat = $latLng[0];
                $model->lng = $latLng[1];
                $images = Oss::uploads($model, $model->studio_id, 'registration', 'pic');
                $model->pic = $images[0];
                $model->save();
                return $this->redirect(['registration-info-view', 'studio_id' => Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id]);
            }else{
                return $this->render('registration/create', [
                    'model' => $model,
                ]);
            }
            return $this->render('registration/create', [
                    'model' => $model,
                ]);
        }
    }

    //查看
    public function actionRegistrationInfoView(){
        $admin_id = Yii::$app->user->identity->id;
        $model = Registration::findOne(['studio_id'=>Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id]);
        return $this->render('registration/view', [
            'model' => $model,
        ]);
    }

    //---------------------------------------专业介绍------------------------------------------------//

    //列表
    public function actionProintroduction()
    {
        $searchModel = new ProintroductionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('prointroduction/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    // //置顶
    // public function actionNewListupp()
    // {
    //     $new = NewList::findOne($_GET['id']);
    //     $new->updated_at = time();
    //     $new->save();
    // }
    //创建
    public function actionCreateProintroduction(){
        $model = new Prointroduction();
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('Prointroduction')['name'];
            $model->url = Yii::$app->request->post('Prointroduction')['url'];
            $model->is_banner = Yii::$app->request->post('Prointroduction')['is_banner'];
            $model->is_top = Yii::$app->request->post('Prointroduction')['is_top'];
            $model->desc = Yii::$app->request->post('Prointroduction')['desc'];
            $model->is_push = Yii::$app->request->post('Prointroduction')['is_push'];
            $model->timing_push_time = Yii::$app->request->post('Prointroduction')['timing_push_time'];
            $admin_id = Yii::$app->user->identity->id;
            //推送
            //如果选择定时推送时间 
            if($model->is_push == 2 || !empty($model->timing_push_time)){
                $model->timing_push_time = (!empty($model->timing_push_time))?$model->timing_push_time:date('Y-m-d H:i:s',time());
            }

            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'prointroduction', 'thumbnails');
            $model->thumbnails = $images[0];
            $model->save();
            return $this->redirect(['view-prointroduction', 'prointroduction_id' => $model->prointroduction_id]);
        } else {
            return $this->render('prointroduction/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdateProintroduction($prointroduction_id){
        $model = Prointroduction::findOne($prointroduction_id);
        //记录老定时推送状态
        $old_is_push = $model->is_push;
        //记录老定时推送时间
        $old_timing_push_time = $model->timing_push_time;
        if (!empty(Yii::$app->request->post())) {

            $model->is_push = Yii::$app->request->post('Prointroduction')['is_push'];
            $model->timing_push_time = Yii::$app->request->post('Prointroduction')['timing_push_time'];
            

            //修改选择定时推送时间
            if($model->is_push != $old_is_push || $model->timing_push_time != $old_timing_push_time){
                if($model->is_push == 2 || !empty($model->timing_push_time)){
                    $model->timing_push_time = (!empty($model->timing_push_time))?$model->timing_push_time:date('Y-m-d H:i:s',time());
                }else{
                    $model->timing_push_time = '';
                }
            }
            $model->name = Yii::$app->request->post('Prointroduction')['name'];
            $model->url = Yii::$app->request->post('Prointroduction')['url'];
            $model->is_banner = Yii::$app->request->post('Prointroduction')['is_banner'];
            $model->is_top = Yii::$app->request->post('Prointroduction')['is_top'];
            $model->desc = Yii::$app->request->post('Prointroduction')['desc'];
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'prointroduction', 'thumbnails');
            if(!empty($images)){
                $model->thumbnails = $images[0];
            }
            $model->save();
            return $this->redirect(['view-prointroduction', 'prointroduction_id' => $model->prointroduction_id]);
        } else {
            return $this->render('prointroduction/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewProintroduction($prointroduction_id){
        $model = Prointroduction::findOne($prointroduction_id);
        return $this->render('prointroduction/view', [
            'model' => $model,
        ]);
    }
    //删除!
    public function actionDelProintroduction($prointroduction_id,$get){
        $model = Prointroduction::findOne($prointroduction_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();


        //获取首页数值
        $get = json_decode($get);
        if (is_object($get)) {
            foreach ($get as $key => $value) {
                if (is_object($value)) {
                    foreach ($value as $k => $v) {
                        $array[$key][$k] = $v;
                    }
                }else{
                    $array[$key] = $value;
                }
                
            }
        }else {
            $array = $get;
        }
        $arr =array_merge_recursive(array(0=>'prointroduction'),$array);
        return $this->redirect($arr);
    }

    //---------------------------------------新闻资讯------------------------------------------------//

    //列表
    public function actionNewList()
    {
        $searchModel = new NewListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('new_list/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    //置顶
    public function actionNewListupp()
    {
        $new = NewList::findOne($_GET['id']);
        $new->updated_at = time();
        $new->save();
    }
    //创建
    public function actionCreateNewList(){
        $model = new NewList();
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('NewList')['name'];
            $model->url = Yii::$app->request->post('NewList')['url'];
            $model->is_banner = Yii::$app->request->post('NewList')['is_banner'];
            $model->is_top = Yii::$app->request->post('NewList')['is_top'];
            $model->desc = Yii::$app->request->post('NewList')['desc'];
            $model->is_push = Yii::$app->request->post('NewList')['is_push'];
            $model->timing_push_time = Yii::$app->request->post('NewList')['timing_push_time'];
            $admin_id = Yii::$app->user->identity->id;
            //推送
            //如果选择定时推送时间 
            if($model->is_push == 2 || !empty($model->timing_push_time)){
                $model->timing_push_time = (!empty($model->timing_push_time))?$model->timing_push_time:date('Y-m-d H:i:s',time());
            }

            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'new', 'thumbnails');
            $model->thumbnails = $images[0];
            $model->save();
            return $this->redirect(['view-new-list', 'new_list_id' => $model->new_list_id]);
        } else {
            return $this->render('new_list/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdateNewList($new_list_id){
        $model = NewList::findOne($new_list_id);
        //记录老定时推送状态
        $old_is_push = $model->is_push;
        //记录老定时推送时间
        $old_timing_push_time = $model->timing_push_time;
        if (!empty(Yii::$app->request->post())) {

            $model->is_push = Yii::$app->request->post('NewList')['is_push'];
            $model->timing_push_time = Yii::$app->request->post('NewList')['timing_push_time'];
            

            //修改选择定时推送时间
            if($model->is_push != $old_is_push || $model->timing_push_time != $old_timing_push_time){
                if($model->is_push == 2 || !empty($model->timing_push_time)){
                    $model->timing_push_time = (!empty($model->timing_push_time))?$model->timing_push_time:date('Y-m-d H:i:s',time());
                }else{
                    $model->timing_push_time = '';
                }
            }
            $model->name = Yii::$app->request->post('NewList')['name'];
            $model->url = Yii::$app->request->post('NewList')['url'];
            $model->is_banner = Yii::$app->request->post('NewList')['is_banner'];
            $model->is_top = Yii::$app->request->post('NewList')['is_top'];
            $model->desc = Yii::$app->request->post('NewList')['desc'];
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'new', 'thumbnails');
            if(!empty($images)){
                $model->thumbnails = $images[0];
            }
            $model->save();
            return $this->redirect(['view-new-list', 'new_list_id' => $model->new_list_id]);
        } else {
            return $this->render('new_list/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewNewList($new_list_id){
        $model = NewList::findOne($new_list_id);
        return $this->render('new_list/view', [
            'model' => $model,
        ]);
    }
    //删除!
    public function actionDelNewList($new_list_id,$get){
        $model = NewList::findOne($new_list_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();


        //获取首页数值
        $get = json_decode($get);
        if (is_object($get)) {
            foreach ($get as $key => $value) {
                if (is_object($value)) {
                    foreach ($value as $k => $v) {
                        $array[$key][$k] = $v;
                    }
                }else{
                    $array[$key] = $value;
                }
                
            }
        }else {
            $array = $get;
        }
        $arr =array_merge_recursive(array(0=>'new-list'),$array);
        return $this->redirect($arr);
    }

    //---------------------------------------辉煌成绩------------------------------------------------//

    //列表
    public function actionPrizeList()
    {
        $searchModel = new PrizeListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('prize_list/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //创建
    public function actionCreatePrizeList(){
        $model = new PrizeList();
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('PrizeList')['name'];
            $model->url = Yii::$app->request->post('PrizeList')['url'];
            $model->is_banner = Yii::$app->request->post('PrizeList')['is_banner'];
            $model->desc = Yii::$app->request->post('PrizeList')['desc'];
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'prize', 'thumbnails');
            $model->thumbnails = $images[0];
            $model->save();
            return $this->redirect(['view-prize-list', 'prize_list_id' => $model->prize_list_id]);
        } else {
            return $this->render('prize_list/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdatePrizeList($prize_list_id){
        $model = PrizeList::findOne($prize_list_id);
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('PrizeList')['name'];
            $model->url = Yii::$app->request->post('PrizeList')['url'];
            $model->is_banner = Yii::$app->request->post('PrizeList')['is_banner'];
            $model->desc = Yii::$app->request->post('PrizeList')['desc'];
            $admin_id = Yii::$app->user->identity->id;
            $model->admin_id = $admin_id;

            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'prize', 'thumbnails');
            if(!empty($images)){
                $model->thumbnails = $images[0];
            }
            $model->save();
            return $this->redirect(['view-prize-list', 'prize_list_id' => $model->prize_list_id]);
        } else {
            return $this->render('prize_list/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewPrizeList($prize_list_id){
        $model = PrizeList::findOne($prize_list_id);
        return $this->render('prize_list/view', [
            'model' => $model,
        ]);
    }
    //删除
    public function actionDelPrizeList($prize_list_id){
        $model = PrizeList::findOne($prize_list_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();
        return $this->redirect(['prize-list']);
    }


    //-------------------------------------教师团队--------------------------------------------------//
    //列表
    public function actionTeacherList()
    {
        $searchModel = new TeacherListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('teacher_list/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //创建
    public function actionCreateTeacherList(){
        $model = new TeacherList();
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('TeacherList')['name'];
            $model->auth = Yii::$app->request->post('TeacherList')['auth'];
            $model->desc = Yii::$app->request->post('TeacherList')['desc'];
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'teacher', 'pic_url');
            $model->pic_url = $images[0];
            $model->save();
            return $this->redirect(['view-teacher-list','teacher_list_id' => $model->teacher_list_id]);
        } else {
            return $this->render('teacher_list/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdateTeacherList($teacher_list_id){
        $model = TeacherList::findOne($teacher_list_id);
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('TeacherList')['name'];
            $model->auth = Yii::$app->request->post('TeacherList')['auth'];
            $model->desc = Yii::$app->request->post('TeacherList')['desc'];
            $admin_id = Yii::$app->user->identity->id;
            $model->admin_id = $admin_id;

            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'teacher', 'pic_url');
            if(!empty($images)){
                $model->pic_url = $images[0];
            }
            $model->save();
            return $this->redirect(['view-teacher-list', 'teacher_list_id' => $teacher_list_id]);
        } else {
            return $this->render('teacher_list/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewTeacherList($teacher_list_id){
        $model = TeacherList::findOne($teacher_list_id);
        return $this->render('teacher_list/view', [
            'model' => $model,
        ]);
    }
    //删除
    public function actionDelTeacherList($teacher_list_id){
        $model = TeacherList::findOne($teacher_list_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();
        return $this->redirect(['teacher-list']);
    }
    //-------------------------------------优秀作品--------------------------------------------------//
    //列表
    public function actionWorksList()
    {
        $searchModel = new WorksListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('works_list/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //创建
    public function actionCreateWorksList(){
        $model = new WorksList();
        if (!empty(Yii::$app->request->post())) {

            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'works', 'pic_url');
            foreach ($images as $image) {
                $_model = clone $model;
                $_model->pic_url = $image;
                $_model->name = Yii::$app->request->post('WorksList')['name'];
                $_model->type = Yii::$app->request->post('WorksList')['type'];
                $_model->is_teacher = Yii::$app->request->post('WorksList')['is_teacher'];
                $_model->admin_id = $admin_id;
                $_model->studio_id = $studio;
                
                $_model->save();
            }
            return $this->redirect(['works-list']);
        } else {
            return $this->render('works_list/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdateWorksList($works_list_id){
        $model = WorksList::findOne($works_list_id);
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('WorksList')['name'];
            $model->desc = Yii::$app->request->post('WorksList')['desc'];
            $model->type = Yii::$app->request->post('WorksList')['type'];
            $model->is_teacher = Yii::$app->request->post('WorksList')['is_teacher'];
            $admin_id = Yii::$app->user->identity->id;
            $model->admin_id = $admin_id;

            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'works', 'pic_url');
            if(!empty($images)){
                $model->pic_url = $images[0];
            }
            $model->save();
            return $this->redirect(['view-works-list', 'works_list_id' => $works_list_id]);
        } else {
            return $this->render('works_list/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewWorksList($works_list_id){
        $model = WorksList::findOne($works_list_id);
        return $this->render('works_list/view', [
            'model' => $model,
        ]);
    }
    //删除
    public function actionDelWorksList($works_list_id,$get){
        $model = WorksList::findOne($works_list_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();

        //获取首页数值
        $get = json_decode($get);
        if (is_object($get)) {
            foreach ($get as $key => $value) {
                if (is_object($value)) {
                    foreach ($value as $k => $v) {
                        $array[$key][$k] = $v;
                    }
                }else{
                    $array[$key] = $value;
                }
                
            }
        }else {
            $array = $get;
        }
        $arr =array_merge_recursive(array(0=>'works-list'),$array);
        return $this->redirect($arr);
    }

    //-------------------------------------校园环境--------------------------------------------------//
    //列表
    public function actionSchoolPic()
    {
        $searchModel = new SchoolPicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('school_pic/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //创建
    public function actionCreateSchoolPic(){
        $model = new SchoolPic();
        if (!empty(Yii::$app->request->post())) {
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            
            $images = Oss::uploads($model, $studio, 'school', 'pic_url');

            foreach ($images as $image) {

                $_model = clone $model;
                $_model->pic_url = $image;

                $_model->desc = Yii::$app->request->post('SchoolPic')['desc'];
                $_model->type = Yii::$app->request->post('SchoolPic')['type'];
                $_model->admin_id = $admin_id;
                $_model->studio_id = $studio;
                
                
                $_model->save();
            }
            
            return $this->redirect(['school-pic']);
        } else {
            return $this->render('school_pic/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdateSchoolPic($school_pic_id){
        $model = SchoolPic::findOne($school_pic_id);
        if (!empty(Yii::$app->request->post())) {
            $model->desc = Yii::$app->request->post('SchoolPic')['desc'];
            $model->type = Yii::$app->request->post('SchoolPic')['type'];
            $admin_id = Yii::$app->user->identity->id;
            $model->admin_id = $admin_id;


            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'school', 'pic_url');
            if(!empty($images)){
                $model->pic_url = $images[0];
            }
            $model->save();
            return $this->redirect(['view-school-pic', 'school_pic_id' => $school_pic_id]);
        } else {
            return $this->render('school_pic/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewSchoolPic($school_pic_id){
        $model = SchoolPic::findOne($school_pic_id);
        return $this->render('school_pic/view', [
            'model' => $model,
        ]);
    }
    //删除
    public function actionDelSchoolPic($school_pic_id){
        $model = SchoolPic::findOne($school_pic_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();
        return $this->redirect(['school-pic']);
    }

    //---------------------------------------招生简章------------------------------------------------//

    //列表
    public function actionEnrollmentGuideList()
    {
        $searchModel = new EnrollmentGuideListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('enrollment_guide_list/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //创建
    public function actionCreateEnrollmentGuideList(){
        $model = new EnrollmentGuideList();
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('EnrollmentGuideList')['name'];
            $model->url = Yii::$app->request->post('EnrollmentGuideList')['url'];
            $model->is_banner = Yii::$app->request->post('EnrollmentGuideList')['is_banner'];
            $model->desc = Yii::$app->request->post('EnrollmentGuideList')['desc'];
            $model->is_top = Yii::$app->request->post('EnrollmentGuideList')['is_top'];
            $admin_id = Yii::$app->user->identity->id;
            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $model->admin_id = $admin_id;
            $model->studio_id = $studio;
            $images = Oss::uploads($model, $studio, 'enrollment', 'thumbnails');
            $model->thumbnails = $images[0];
            $model->save();
            return $this->redirect(['view-enrollment-guide-list', 'enrollment_guide_list_id' => $model->enrollment_guide_list_id]);
        } else {
            return $this->render('enrollment_guide_list/create', [
                'model' => $model,
            ]);
        }
    }
    //修改
    public function actionUpdateEnrollmentGuideList($enrollment_guide_list_id){
        $model = EnrollmentGuideList::findOne($enrollment_guide_list_id);
        if (!empty(Yii::$app->request->post())) {
            $model->name = Yii::$app->request->post('EnrollmentGuideList')['name'];
            $model->url = Yii::$app->request->post('EnrollmentGuideList')['url'];
            $model->is_banner = Yii::$app->request->post('EnrollmentGuideList')['is_banner'];
            $model->desc = Yii::$app->request->post('EnrollmentGuideList')['desc'];
            $model->is_top = Yii::$app->request->post('EnrollmentGuideList')['is_top'];
            $admin_id = Yii::$app->user->identity->id;
            $model->admin_id = $admin_id;


            $studio = Campus::findOne(Admin::findOne($admin_id)->campus_id)->studio_id;
            $images = Oss::uploads($model, $studio, 'enrollment', 'thumbnails');
            if(!empty($images)){
                $model->thumbnails = $images[0];
            }
            $model->save();
            return $this->redirect(['view-enrollment-guide-list', 'enrollment_guide_list_id' => $model->enrollment_guide_list_id]);
        } else {
            return $this->render('enrollment_guide_list/update', [
                'model' => $model,
            ]);
        }
    }
    //查看
    public function actionViewEnrollmentGuideList($enrollment_guide_list_id){
        $model = EnrollmentGuideList::findOne($enrollment_guide_list_id);
        return $this->render('enrollment_guide_list/view', [
            'model' => $model,
        ]);
    }
    //删除
    public function actionDelEnrollmentGuideList($enrollment_guide_list_id){
        $model = EnrollmentGuideList::findOne($enrollment_guide_list_id);
        $model->status==10?$model->status=0:$model->status=10;
        $model->save();
        return $this->redirect(['view-enrollment-guide-list']);
    }



   //-------------------------------------报名--------------------------------------------------//



    /**
     * Lists all News models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NewsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionXinwenzixun()
    {
        $searchModel = new Xinwenzixun();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('xinwenzixun_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreateXinwenzixun(){
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
        $data = getStudio($admin_id);
    }

    public function actionYuanxiaobaokao()
    {
        $searchModel = new NewsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('xinwenzixun_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Displays a single News model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new News model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new News();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing News model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing News model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the News model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return News the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = News::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
