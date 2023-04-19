<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use components\Jpush;
use common\models\Format;

class BuyRecord extends \common\models\BuyRecord
{
    public function beforeSave($insert)
    {
        return true;
    }

    public function fields() {

        $fields = parent::fields();

        
        return $fields;
    }
    public static function GetBuyStatus($gather_id,$user_id,$role) {

        $gather =  self::findOne(['buy_id'=>$user_id,'role'=>$role,'gather_id'=>$gather_id,'status'=>10]);

        if($gather){
            if(Format::addYears($gather->created_at,1) > time()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //获取画室是否购买云课件
    public static function GetBuyStudio($gather_id,$studio_id,$admin_id) {

        if($studio_id != 183){
            $gather = self::find()
                        ->where(['buy_studio'=>$studio_id,'gather_id'=>$gather_id,'status'=>10,'role'=>10])
                        ->andWhere(['>','active_at',time()])
                        ->one();
        }else{
            $gather = self::find()
                        ->where(['buy_id'=>$admin_id,'gather_id'=>$gather_id,'status'=>10,'role'=>10])
                        ->andWhere(['>','active_at',time()])
                        ->one(); 
        }


        $is_cloud  =  Admin::findOne($admin_id)->is_cloud ? true : false;
        if($is_cloud){
            return  $gather ? true : false;
        }else{
            return false;
        }

    }

    //获取课件过期时间
    public static function GetBuyTime($gather_id,$user_id) {

        $gather =  self::findOne(['buy_id'=>$user_id,'role'=>20,'gather_id'=>$gather_id,'status'=>10]);

        return date('Y/m/d',Format::addYears($gather->created_at,1));
    }
    //获取过期时间
    public function getGathers()
    {
        return $this->hasOne(Gather::className(), ['id' => 'gather_id'])->alias('gathers');
    }
}