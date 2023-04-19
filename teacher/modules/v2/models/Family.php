<?php

namespace teacher\modules\v2\models;
use Yii;
use components\Oss;

class Family extends \common\models\Family
{
    public $studio_name;
    public function fields()
	{
	    $fields = parent::fields();
        $fields['admin_id']= function() {
            return $this->id;
        };
        $fields['pic_url'] = function () {
            $size = Yii::$app->params['oss']['Size']['320x320'];
            if($this->image){
                return Oss::getUrl('family', 'picture', 'image', $this->image).$size;
            }else{
                return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
            }
        };
        $fields['campus_id'] = function() {
            return Campus::findOne($this->campus_id)->name;
        };

        // if($this->studio_id){
        //     $fields['studio_id'] = function() {
        //         return $this->studio_id;
        //     };
        // }else{
        //     $fields['studio_id'] = function() {
        //         return Campus::findOne($this->campus_id)->studio_id;
        //     };
        // }
            if($this->campus_id){
                $fields['studio_id'] = function() {
                    return Campus::findOne($this->campus_id)->studio_id;
                };
            }

	    $fields['user_role'] = function () {
	    	return 'family';
	    };
        $fields['province'] = function () {
            return $this->citys->name;
        };
        $fields['is_vip'] = function () {
            if(!empty($this->vip_time) && strtotime($this->vip_time) > time()){
                return true;
            }else{
                return false;
            }
            
        }; 
        $fields['gender'] = function () {
            return self::getValues('gender',$this->gender);
        };
        $fields['identifier'] = function () {

            return 'family'.$this->id;
        };
        $fields['campusId_Rn'] = function () {
            return $this->campus_id;
        };
        if($this->studio_name) {
            $fields['studio_name'] = function () {
                return $this->studio_name;
            };   
        }
        // if($this->relation_id) {
        //     $fields['code_number'] = function() {
        //         return ActivationCode::findOne(['relation_id'=>$this->relation_id,'type'=>2,'status'=>10])->code;
        //     };
        // }else{
        //     $fields['code_number'] = function() {

        //         return NULL;
        //     }; 
        // }


        $fields['is_band']      = function() {
            return $this->relation_id ? true : false;
        };

        $fields['student_name'] = function() {
            return $this->relation_id ? User::findOne($this->relation_id)->name : "未绑定";
        };
	    unset(
            $fields['id'],
            $fields['image'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}
    public function getCitys()
    {
        return $this->hasOne(City::className(),['id'=>'province'])->alias('citys');
    }
    
    public static function getPic($studio,$image) {
        $size = Yii::$app->params['oss']['Size']['320x320'];
        return Oss::getUrl($studio, 'picture', 'image', $image).$size;
    }
}
