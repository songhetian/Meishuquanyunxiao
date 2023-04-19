<?php

namespace api\modules\v1\models;

use Yii;
use yii\helpers\Html;
use backend\models\Admin;
use common\models\Campus;
use components\Oss;
use components\Spark;

class Video extends \common\models\Video
{
    public function fields()
	{
	    $fields = parent::fields();
	    
	    $fields['preview'] = function () {
			$size = Yii::$app->params['oss']['Size']['750x500'];
            $studio = Campus::findOne(Admin::findOne($this->admin_id)->campus_id)->studio_id;
	    	return Oss::getUrl($studio, 'video', 'preview', $this->preview).$size;
        };

        $fields['cc_id'] = function () {
            return "<!DOCTYPE html><html><head></head><body style='margin:0;padding:0; background:black;'>".Spark::getPlayCode($this->cc_id, $this->charging_option)."</body></html>";
        };

        $fields['cc_id_original'] = function(){
        	return $this->cc_id;
        };

        $fields['duration'] = function(){
        	return Spark::getDuration($this->cc_id, $this->charging_option);
        };
        
        $fields['studio_id'] = function () {
            return ($studio_id) ? $studio_id : Yii::t('api', 'Local Studio');
        };

        $fields['watch_count'] = function () {
        	return $this->watch_count + 1;
        };

        $fields['description'] = function () {
        	$host_info = Yii::$app->request->hostInfo.'/assets';
        	$description = preg_replace('/api.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->description));
            return preg_replace('/(http|https):\/\//', 'http://', $description);
        };
        if(!Spark::getDuration($this->cc_id, $this->charging_option))
        {   
            if($this->charging_option == self::CHARGING_NORMAL){
               $this->charging_option = self::CHARGING_ENCRYPT;
            }elseif($this->charging_option == self::CHARGING_ENCRYPT){
               $this->charging_option = self::CHARGING_NORMAL;
            }

        }

	    unset(
	    	$fields['source'],
	    	$fields['metis_material_id'],
	    	$fields['category_id'],
	    	$fields['keyword_id'],
	    	$fields['admin_id'],
	    	$fields['is_public'],
	    	$fields['created_at'], 
	    	$fields['updated_at'], 
	    	$fields['status']
	    );
	    return $fields;
	}
}
