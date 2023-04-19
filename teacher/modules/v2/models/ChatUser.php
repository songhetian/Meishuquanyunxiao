<?php 
	
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;

class ChatUser extends \common\models\User
{
	public function fields()
	{
		$fields = parent::fields();

		$fields['identifier'] = function () {

			return 'student'.$this->admin_id;
		};

        $fields['user_role'] = function() {
            return 'student';
        };
        
        $fields['pic_url'] = function () {
            $size = Yii::$app->params['oss']['Size']['320x320'];
            if($this->image){
                if($this->is_image){
                        $studio = 'student';
                    }else{
                        $studio = $this->studio_id;  
                    }
            	return Oss::getUrl($studio, 'picture', 'image', $this->image).$size;
            }else{
            	return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
            }
        };

		return $fields;
	}

    public static function getStudent($studio_id) {

        $users =  self::find()
                        ->select('id')
                        ->where(['studio_id'=>$studio_id,'status'=>10,'campus_id'=>NULL,'class_id'=>NULL])
                        ->asArray()
                        ->all();
        return array_column($users, 'id');

    }
}

?>