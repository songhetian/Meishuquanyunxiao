<?php
namespace teacher\modules\v2\controllers;

use Yii;
use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use teacher\modules\v2\models\User;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\CourseMaterial;
use teacher\modules\v2\models\CourseCollection;
use teacher\modules\v1\models\CourseMaterialInfo;
use teacher\modules\v2\models\SimpleCourseMaterial;


class CourseCollectionController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\CourseCollection';

    public function actionList($search = '',$is_show = 0,$page=0,$limit=10)
    {

        $modelClass = $this->modelClass;

        $model = new CourseCollection();

        if($this->user_role == 20){

          $list =  array_keys($modelClass::find()
                         ->joinWith('materials')
                         ->joinWith('admins')
                         ->where(['course_collection.status'=>10,'course_collection.admin_id'=>$this->user_id])
                         ->andFilterwhere(['or',
                                          ['like','materials.name',$search],
                                          ['like','admins.name',$search]
                          ])
                         ->orderBy("course_collection.created_at DESC")
                         ->indexBy('material_id')
                         ->all());
        }elseif($this->user_role == 10) {
          $list =  array_keys(CourseMaterial::find()
                         ->where(['status'=>10,'admin_id'=>$this->user_id])
                         ->andFilterwhere(['or',
                                          ['like','materials.name',$search],
                          ])
                         ->orderBy("created_at DESC")
                         ->indexBy('id')
                         ->all());
        }

        CourseMaterial::$is_show = $is_show;

        if($is_show){
          $data = SimpleCourseMaterial::find()
                  ->where(['id'=>$list,'status'=>CourseMaterial::STATUS_ACTIVE])
                  ->orderBy("updated_at DESC,created_at DESC");
        }else{
          $data = CourseMaterial::find()
                  ->where(['id'=>$list,'status'=>CourseMaterial::STATUS_ACTIVE])
                  ->orderBy("updated_at DESC,created_at DESC");
        }
            
       $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $limit]);

       $offset = ($page*$limit);

       $model = $data->offset($offset)->limit($pages->limit)->all();

       $_GET['message'] = Yii::t('teacher','Sucessfully List');

       return $model;
    }

    public function actionAdd($material_id)
    {

        $modelClass = $this->modelClass;

        $model = new CourseCollection();

        $model->material_id = $material_id;

        $model->studio_id   = User::findOne($this->user_id)->studio_id;

        $model->admin_id = $this->user_id;

        $model->teacher_id = CourseMaterial::findOne($model->material_id)->admin_id;

        if($model->save()) {
          return SendMessage::sendSuccessMsg("收藏成功");
        }else{
          // if($model->getErrors('material_id')){
          //   return SendMessage::sendErrorMsg($model->getErrors('material_id')[0]);
          // }
          return $model->getErrors();
          return SendMessage::sendErrorMsg("收藏失败");
        }
    }

    public function actionDel($material_id) {

      $modelClass = $this->modelClass;

      $model = $modelClass::findOne(['material_id'=>$material_id,'admin_id'=>$this->user_id]);

      if(!$model) {
        return SendMessage::sendSuccessMsg("该课件已经删除或者不存在!");
      }

      $model->status = 0;

      if($model->save()) {
          return SendMessage::sendSuccessMsg("删除成功");
      }else{
          return SendMessage::sendErrorMsg("删除失败");
      }

    }
}