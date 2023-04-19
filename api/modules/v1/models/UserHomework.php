<?php

namespace api\modules\v1\models;

use Yii;
use common\models\User;
use common\models\Campus;
use components\Oss;

class UserHomework extends \common\models\UserHomework
{
	const SCOPE_ME = 10;
    const SCOPE_OTHER = 20;

    public function fields()
	{
	    $fields = parent::fields();

	    if($this->users){
            $fields['user_id'] = function () {
                return [
                    'id' => $this->users->id,
                    'name' => $this->users->name
                ];
            };
        }
        $fields['homework_id'] = function() {
            return $this->id;
        };
        if(!empty($this->image)){
            $fields['image'] = function () {
                $size = Yii::$app->params['oss']['Size']['350x350'];
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->image).$size;
            };
            $fields['image_original'] = function () {
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->image);
            };
        }
        if(!empty($this->comment_image)){
            $fields['comment_image'] = function () {
                $size = Yii::$app->params['oss']['Size']['350x350'];
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->comment_image).$size;
            };
            $fields['comment_image_original'] = function () {
                $studio = Campus::findOne(User::findOne($this->user_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'user-homework', 'image', $this->comment_image);
            };
        }
        if(!empty($this->video)){
            $fields['video'] = function () {
                return (Object)[
                    'cc_id' => $this->video,
                    'charging_option' => $this->charging_option,
                    'duration' =>  Spark::getDuration($this->video, $this->charging_option*10),
                ];
            };
        }

        $fields['user_name'] = function () {

            return $this->users->name;
        };

        $fields['created_at'] = function () {
            return date("Y.m.d", $this->created_at);
        };
        if(!empty($this->evaluator)){
            $fields['evaluator'] = function () {
                return[
                    'evaluator_id' => $this->evaluators->id,
                    'name'        => $this->evaluators->name
                ];
            };
        }
        unset(
            $fields['course_material_id'],
            $fields['evaluator'],
            $fields['updated_at'], 
            $fields['status'],
            $fields['cc_id']
        );
        return $fields;
	    return $fields;
	}
}
