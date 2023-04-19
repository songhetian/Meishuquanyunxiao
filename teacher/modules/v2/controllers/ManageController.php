<?php
namespace teacher\modules\v2\controllers;

use Yii;
use teacher\modules\v1\models\SendMessage;
use common\models\Campus;
use common\models\User;
use teacher\modules\v2\models\Admin;
use teacher\modules\v2\models\CodeAdmin;
use teacher\modules\v2\models\Family;
use common\models\Format;

class ManageController extends MainController
{
	public $modelClass = 'teacher\modules\v2\models\Manage';

	public function actionSearch()
    {
    	$user_role = Yii::$app->request->post('user_role');
    	$studio_id = Yii::$app->request->post('studio_id');
    	$campus_id = Yii::$app->request->post('campus_id');
    	$admin_id  = Yii::$app->request->post('admin_id');

    	if($user_role == 'teacher'){
            $code_Admin = CodeAdmin::findOne($admin_id);
    		$item_name = $code_Admin->auths->item_name;
			$pid = substr($item_name, -3);
    		if($studio_id == 183 && $pid == '009') {
                return SendMessage::sendErrorMsg("没有权限");
    		}
    	}

    	$time = date('Y-m-d',time());
    	$start = strtotime($time);
    	$end = strtotime("+1 day",strtotime($time))-1;

    	$total_teacher = Admin::find()
        ->where([
            'studio_id' => $studio_id,
            'status' => 10
        ])->count();

    	$today_teacher = Admin::find()
        ->where(['studio_id'=>$studio_id,'status'=>10])
        ->andFilterWhere(['between', 'created_at', $start, $end])
        ->count();

    	$total_student = User::find()
        ->where([
            'studio_id' => $studio_id,
            'status' => 10
        ])->count();

    	$today_sutdent = User::find()
		->where([
            'studio_id' => $studio_id,
            'status' =>10
        ])
		->andFilterWhere([
            'between',
            'created_at',
            $start, $end
        ])
		->count();

    	$total_family = Family::find()
        ->where([
            'studio_id' => $studio_id, 
            'status' => 10
        ])->count();

    	$today_family = Family::find()
		->where(['studio_id' => $studio_id, 'status' => 10])
		 ->andFilterWhere(['between', 'created_at', $start, $end])
		->count();

    	$total = $total_teacher + $total_student + $total_family;

    	if($user_role && $studio_id && $campus_id){
    		$res['dataList'] = Yii::$app->params['manage'][$user_role];
    		if($user_role == 'teacher'){
    			$res['appInfo'] = [
					[
						'title' => '总用户数',
						'UserNum' => $total
					],
					[
						'title'   => '老师数量',
						'UserNum' =>  $total_teacher.'('.$today_teacher.')'
					],
					[
						'title'   => '学生数量',
						'UserNum' => $total_student.'('.$today_sutdent.')'
					],
					[
						'title' => '家长数量',
						'UserNum' => $total_family.'('.$today_family.')'
					]
				];

				if($admin_id){
					$res['hasOwnCampus'] = Campus::findOne($campus_id)->name;
					$admin = Admin::findOne($admin_id);
	    			$res['schoolDataList'] = Campus::find()
		    		->select('id,name')
			    	->where(['studio_id' => $studio_id, 'status' => Campus::STATUS_ACTIVE])
			    	->andFilterWhere(['id' => Format::explodeValue($admin['campus_id'])])
			    	->all();
				}

                // 针对特殊画室进行处理
                if($studio_id == 327) {
                    $role = Yii::$app->authManager->getRolesByUser($admin_id);
                    $des = $role[key($role)]->description;
                    if($des != '校长') {
                        $res['dataList'] = Yii::$app->params['manage']['teacherSpecial'];
                    }
                }
    		}

	    	$_GET['message'] = Yii::t('teacher', 'Search Manage Success');
			return $res;
    	}
    	return '缺少参数';
    }
}