<?php

namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use common\models\Campus;
use common\models\LeaveAudit;
use common\models\Format;
use common\models\Query;

class Leave extends \common\models\Leave
{
    public function fields()
	{
	    $fields = parent::fields();
	    unset(
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
        
	    return $fields;
	}

    //获取请假列表
    public static function createData($account_id, $page = 0, $limit = 3)
    {
        $model = self::find()
        ->where(['account_id' => $account_id, 'status' => self::STATUS_ACTIVE])
        ->offset($page * $limit)
        ->limit($limit)
        ->orderBy('created_at DESC')
        ->all();
        return ($model) ? self::DataInfo($model) : [];
    }

    /**
     * [获取请假数据详情]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2018-05-21
     * @param     [type]        $model  [数据]
     * @param     integer       $source [来源 我审批的/发起 20 详情 30]
     */
    public static function DataInfo($model, $source = 10)
    {
        foreach ($model as $k => $v) {
            $tname = self::getTname($v->user_role);
            $arr[$k] = [
                'id' => $v->id,
                'avatar' => self::getImage($v->user_role, $v->$tname->campus_id, $v->$tname->image),
                'name' => $v->$tname->name,
                'type' => self::getValues('type', $v->type),
                'beginTime' => date('Y-m-d H:i:s', $v->started_at),
                'endTime' => date('Y-m-d H:i:s', $v->ended_at),
            ];
            if($source == 20){
                $arr[$k]['CreateTime'] = date('Y-m-d', $v->created_at);
            }elseif($source == 30){
                $class_name = Query::concatValue(Classes::className(), $v->$tname->class_id);
                $arr[$k] += [
                    'class' => ($class_name) ? $class_name : '全部班级',
                    //'totalDay' => $v->day,
                    'des' => $v->reason
                ];
            }
        
            $arr[$k] += self::getProcessingState($v);
        }
        return ($arr) ? $arr : []; 
    }

    //获取审批状态
    public static function getProcessingState($model, $source = 10)
    {
        $audits = LeaveAudit::getAudits($model->id);
        foreach ($audits as $v) {
            $tname = self::getTname($v->user_role);
            switch ($v->processing_state) {
                case LeaveAudit::PROCESSING_STATE_NOT_YET:
                    $status = $v->$tname->name . ' 审批中';
                    $time = '...';
                    $color = 'orange';
                    if($source == 20){
                        $res[] = self::getSteps('process', $status, $time);
                        break;
                    }else{
                        continue;
                    }

                case LeaveAudit::PROCESSING_STATE_REFUSE:
                    $status = $v->$tname->name . ' 已拒绝';
                    $time = '...';
                    $color = 'red';
                    if($source == 20){
                        $res[] = self::getSteps('error', $status, $time);
                        break;
                    }else{
                        continue;
                    }

                case LeaveAudit::PROCESSING_STATE_CLOSE:
                    $status = $v->$tname->name . ' 已撤销';
                    $time = '...';
                    $color = 'red';
                    if($source == 20){
                        $res[] = self::getSteps('error', $status, $time);
                        break;
                    }else{
                        continue;
                    }

                case LeaveAudit::PROCESSING_STATE_ED:
                    $status = '通过审批';
                    $time = date('Y-m-d', $v->processing_at);
                    $color = 'rgba(85,190,134,1)';
                    if($source == 20){
                        $res[] = self::getSteps('finish', $v->$tname->name . ' 已审批', $time);
                    }
                    break;
            }
        }
        
        if($res){
            return $res;
        }
        return [
            'ProcessingState' => $status,
            'ProcessingTime' => $time,
            'color' => $color
        ];
    }

    public static function getSteps($status, $nameInfo, $timeInfo)
    {
        return [
            'status' => $status,
            'nameInfo' => $nameInfo,
            'timeInfo' => $timeInfo
        ];
    }

    //获取可见范围内数据
    public static function getIds($table, $account_id) 
    {
        $admin = Admin::findOne($account_id);
        $res = $table::find()
        ->andFilterWhere(['or like', Format::concatField('campus_id'), Format::concatString($admin->campus_id)])
        ->andFilterWhere(['or like', Format::concatField('class_id'), Format::concatString($admin->class_id)])
        ->andFilterWhere(['status' => $table::STATUS_ACTIVE])
        ->indexBy('id')
        ->all();

        return array_keys($res);
    }

    //获取表名称
    public static function getTname($user_role)
    {
        switch ($user_role) {
            case 'student':
                $tname = 'users';
                break;
            case 'family':
                $tname = 'familys';
                break;
            case 'teacher':
                $tname = 'admins';
                break;
        }
        return $tname;
    }

    //获取头像地址
    public static function getImage($type, $campus_id = NULL, $image, $field = 'picture') {
        if($image){
            $size = Yii::$app->params['oss']['Size']['320x320'];
            if($type == 'family'){
                return Oss::getUrl('family', $field, 'image', $image).$size;
            }else{
                $studio = Format::getStudio('id', $campus_id);
                return Oss::getUrl($studio, $field, 'image', $image).$size;
            }
        }
        return "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c";
    }
}