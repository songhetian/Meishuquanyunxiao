<?php
namespace teacher\modules\v2\controllers;

use Yii;
use components\Code;
use teacher\modules\v2\models\Admin;
use common\models\Format;
use teacher\modules\v2\models\Rbac;
use yii\base\ErrorException;
use teacher\modules\v1\models\Errors;
use teacher\modules\v2\models\Tencat;
use teacher\modules\v2\models\Campus;
use teacher\modules\v2\models\Studio;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ClassPeriod;
use teacher\modules\v2\models\AuthItemChild;
use teacher\modules\v2\models\AuthAssignment;
use teacher\modules\v2\models\ActivationCode;

class CreateController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\Studio';
	
    /**
     * [actionIndex 创建画室]
     * @copyright [tianhesong]
     * @version   [v1.0]
     * @date      2018-04-04
     */

    public function actionCreateStudio() {

        $connect = Yii::$app->db->beginTransaction();

        try{

            //画室名称
            $name = Yii::$app->request->post('name');
            $phone_number = Yii::$app->request->post('phone_number');

            $teacher_name = Yii::$app->request->post('teacher_name');
            $studio = new Studio();
            $studio->load(Yii::$app->getRequest()->getBodyParams(), '');

            $studio->review_num = Yii::$app->request->post('one_year_num')+Yii::$app->request->post('two_years_num')+Yii::$app->request->post('three_years_num');

            $studio->image = "background.png";
            $studio->teacher_num = ceil($studio->review_num*0.1);
            if(!$studio->save()) {
                throw new ErrorException(Errors::getInfo($studio->getErrors()));
            }
            $studio_id  = $studio->id;

            $studio = Studio::findOne($studio_id);

            $studio->token_value = md5($studio_id.$sutdio->name);

            $code   =  new Code();
            
            $studio->bind_code = 'A'.substr($code->CreateOne(),0,4);
            

            if(!$studio->save()) {
                throw new ErrorException(Errors::getInfo($studio->getErrors()));
            }
            //创建校区

            $campus = new Campus();

            $campus->name      = $name;
            $campus->studio_id = $studio->id;
            $campus->is_main   = 10;

            if(!$campus->save()) {
                throw new ErrorException(Errors::getInfo($campus->getErrors()));
            }

            $campus_id = $campus->id;

            $class_periods = ClassPeriod::findAll(['studio_id'=>22,'status'=>10]);

            $class_period = new ClassPeriod();

            foreach ($class_periods as $key => $value) {
                $_model = clone $class_period;
                $_model->name = $value->name;
                $_model->studio_id = $studio_id;
                $_model->started_at = $value->started_at;
                $_model->dismissed_at = $value->dismissed_at;
                $_model->position = $value->position;
                $_model->created_at = time();
                $_model->updated_at = time();
                if(!$_model->save()) {
                    throw new ErrorException(Errors::getInfo($_model->getErrors()));
                }
            }

            //创建角色

            $auth_item = new Rbac();

            $auth_items =  Rbac::findAll(['studio_id'=>22]);

            foreach ($auth_items as $key => $value) {

                $_model = clone $auth_item;

                $_model->studio_id = $studio_id;

                $_model->name = '17'.sprintf("%04d", $studio_id).sprintf("%03d", $key+1);
      
                if($key == 0) {
                    $_model->pid = 0;
                }else{
                    $_model->pid =  '17'.sprintf("%04d", $studio_id).sprintf("%03d", $key);
                }
                $_model->type = 1;

                $_model->description = $value->description;

                if(!$_model->save()) {
                    throw new ErrorException(Errors::getInfo($_model->getErrors()));
                }
            }

            //创建角色权限
            $auth_item_child = new AuthItemChild();

            for ($i=1; $i < count($auth_items)+1; $i++) {

                $local_parent  =  '17'.sprintf("%04d", 22).sprintf("%03d", $i);

                $target_parent =  '17'.sprintf("%04d", $studio_id).sprintf("%03d", $i);

                $array =  AuthItemChild::findAll(['parent'=>$local_parent]);

                foreach ($array as $key => $value) {
                   
                   $_model = clone $auth_item_child;

                   $_model->parent = $target_parent;

                   $_model->child  = $value->child;

                   if(!$_model->save()) {
                      throw new ErrorException(Errors::getInfo($_model->getErrors()));
                   }
                }
            }

            //添加校长
            $admin = new Admin();

            $admin->phone_number = $phone_number;

            $admin->password_hash = "123456";

            $admin->is_main = 10;

            $admin->name = $teacher_name;

            $admin->campus_id = $campus_id;

            $admin->studio_id = $studio_id;

            $admin->role = '17'.sprintf("%04d", $studio_id).sprintf("%03d", 1);

            if(!$admin->save()) {
              throw new ErrorException(Errors::getInfo($admin->getErrors()));
            }

            $code = new ActivationCode();

            $code->type = 1;
            $code->relation_id = $admin->id;
            $code->activetime = 0;
            $code->studio_id = $studio_id;
            $code->campus_id = $campus_id.'';
            $yazhengma = new Code(1);
            $code->code = $yazhengma->CreateOne();
            if(!$code->save()) {
              throw new ErrorException(Errors::getInfo($code->getErrors()));
            }

            $connect->commit();
            return SendMessage::sendSuccessMsg($code->code);
        }catch (ErrorException $e) {
            $connect->rollBack();
            return SendMessage::sendErrorMsg($e->getMessage());
        }
    }

    //修改全部画室权限
    public function actionChange() {

        $auth_item_child = new AuthItemChild();

        $local_parent  =  '17'.sprintf("%04d", 22).sprintf("%03d", 1);

        $array =  AuthItemChild::find()
                                ->select('child')
                                ->where(['parent'=>$local_parent,'status'=>10])
                                ->asArray()
                                ->all();
        $locl_info = array_column($array, 'child');

        $studio = Studio::find()
                           ->select('id')
                           ->where(['status'=>10])
                           ->asArray()
                           ->all();

        $studios =  array_column($studio, 'id');

        foreach ($studios as $value) {

            $target_parent  =  '17'.sprintf("%04d", $value).sprintf("%03d", 1);

            $array =  AuthItemChild::find()
                                    ->select('child')
                                    ->where(['parent'=>$target_parent,'status'=>10])
                                    ->asArray()
                                    ->all();

            $target = array_column($array, 'child');

            $diff =  array_diff($locl_info, $target);

            $model = new AuthItemChild();
            $model->parent = $target_parent;
            if($diff){
                foreach ($diff as $key => $value) {
                    $_model = clone $model;
                    $_model->child = $value;
                    $_model->save();
                }
            }
        }
    }

}