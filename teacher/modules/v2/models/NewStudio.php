<?php

namespace teacher\modules\v2\models;

use Yii;
use common\models\Course;
use common\models\Format;
use components\Oss;

class NewStudio extends \common\models\Studio
{

  public function fields()
	{
	    $fields = parent::fields();

      $fields['not_teacher_num'] = function() {

          return (int)$this->teacher_num - ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['type'=>1])
                                     ->count();
      };

      $fields['teacher_sc'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['type'=>1])
                                     ->count();
      };
      $fields['teacher_jh'] = function() {
        
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['type'=>1,'is_active'=>10])
                                     ->count();
      };
      $fields['teacher_wjh'] = function() {
        
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['type'=>1,'is_active'=>20])
                                     ->count();
      };
      //一年
      $fields['one_sc'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>1])
                                     ->count();
      };
      $fields['one_jh'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>1,'is_active'=>10])
                                     ->count();
      };
      $fields['one_wjh'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>1,'is_active'=>20])
                                     ->count();
      };

      $fields['not_one_num'] = function() {

          return (int)$this->one_year_num - ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>1])
                                     ->count();
      };
      $fields['not_two_num'] = function() {

          return (int)$this->two_years_num - ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['type'=>2,'activetime'=>2])
                                     ->count();
      };
      //2年
      $fields['two_sc'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>2])
                                     ->count();
      };
      $fields['two_jh'] = function() {
          return (int)ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>2,'is_active'=>10])
                                     ->count();
      };
      $fields['two_wjh'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>2,'is_active'=>20])
                                     ->count();
      };

      //3年
      $fields['three_sc'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>3])
                                     ->count();
      };
      $fields['three_jh'] = function() {
          return (int)ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>3,'is_active'=>10])
                                     ->count();
      };
      $fields['three_wjh'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['type'=>2,'activetime'=>3,'is_active'=>20])
                                     ->count();
      };
      $fields['not_three_num'] = function() {

          return (int)$this->three_years_num - ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['type'=>2,'activetime'=>3])
                                     ->count();
      };

      //3月
      $fields['three_yue_sc'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['activetime'=>0.25])
                                     ->count();
      };
      $fields['three_yue_jh'] = function() {
          return (int)ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['activetime'=>0.25,'is_active'=>10])
                                     ->count();
      };
      $fields['three_yue_wjh'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['activetime'=>0.25,'is_active'=>20])
                                     ->count();
      };
      $fields['not_three_yue_num'] = function() {

          return (int)$this->three_month_num - ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['activetime'=>0.25])
                                     ->count();
      };

     //1月
      $fields['one_yue_sc'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['activetime'=>0.09])
                                     ->count();
      };
      $fields['one_yue_jh'] = function() {
          return (int)ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['activetime'=>0.09,'is_active'=>10])
                                     ->count();
      };
      $fields['one_yue_wjh'] = function() {
          return (int) ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                      ->andWhere(['activetime'=>0.09,'is_active'=>20])
                                     ->count();
      };
      $fields['not_one_yue_num'] = function() {

          return (int)$this->one_month_num - ActivationCode::find()
                                     ->where(['status'=>10,'studio_id'=>$this->id])
                                     ->andWhere(['activetime'=>0.09])
                                     ->count();
      };

  
	    unset(
	    	$fields['jpush_app_key'],
	    	$fields['jpush_master_secret'],
        $fields['review_num'],
        $fields['created_at'],
        $fields['updated_at'],
        $fields['status'],
        $fields['one_month_num'],
        $fields['three_month_num'],
        $fields['six_month_num'],
        $fields['type'],
        $fields['bind_code'],
        $fields['is_press'],
        $fields['image'],
        $fields['token_value'],
        $fields['studio_type'],
        $fields['is_view']
        
      );
	    return $fields;
	}
}
