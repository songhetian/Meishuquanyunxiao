<?php

namespace api\modules\v1\models;

use Yii;
use backend\models\Admin;
use common\models\User;
use common\models\Campus;
use common\models\Course;
use common\models\Format;
use components\Oss;

class Studio extends \common\models\Studio
{

    public function fields()
	{
	    $fields = parent::fields();

	    $fields['image'] = function () {
	    	$prefix = Yii::$app->request->hostInfo.'/assets/images/icon/';
	    	return ($this->image) ? $prefix.$this->image : $prefix.'background.png';
        };

	    $fields['name'] = function () {
            return $this->name;
        };

        $fields['started_at'] = function () {
        	$time = $this->getTime();
        	return $time['started_at'];
        };

        $fields['ended_at'] = function () {
        	$time = $this->getTime();
        	return $time['ended_at'];
        };

	    unset(
	    	$fields['review_num'],
	    	$fields['jpush_app_key'],
	    	$fields['jpush_master_secret'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}

	/**
	 * [getTime 获取用户可见的课程时间]
	 * @copyright [CraZyDoubLe]
	 * @version   [v1.0]
	 * @date      2017-03-20
	 */
	public function getTime(){
		$course = self::getCourseList(Yii::$app->request->get('studio_id'));
		//判断是否为全部可见
		$user = User::findIdentity(Yii::$app->request->get('user_id'));
		$start = ($user->is_all_visible == User::ALL_VISIBLE) ? $course->min('started_at') : $user->created_at;
		$end = $course->max('ended_at');
		//针对 时间不正常 进行处理
		$started_at = ($start > 1451606400) ? $start : 1451606400; 
		$ended_at = ($end < 1735689600) ? $end : 1735689600; 
    	//针对 注册时间 > 最后一天的课程时间 进行处理
    	if($ended_at < $started_at){
    		$ended_at = $started_at;
    	}
		return [
			'started_at' => date("Y.m.d", $started_at),
			'ended_at' => date('Y.m.d', $ended_at)
		];
	}

	public static function getCourseList($studio_id)
    {
        $campus_id = Campus::find()
            ->where(['studio_id' => $studio_id,'status' => Campus::STATUS_ACTIVE])
            ->indexBy('id')
            ->all();
        $campus_id = array_keys($campus_id);

        $admin_id = Admin::find()
            ->where(['or like', Format::concatField('campus_id'), Format::concatString($campus_id)])
            ->indexBy('id')
            ->all();

        $admin_id = array_keys($admin_id);
        return Course::find()
            ->andFilterWhere(['admin_id' => $admin_id])
            ->andFilterWhere(['status' => self::STATUS_ACTIVE]);
    }
}
