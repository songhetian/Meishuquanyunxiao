<?php

namespace teacher\modules\v2\models;

use teacher\modules\v2\models\CodeAdmin;
use common\models\Classes;

class Credit extends \common\models\Credit
{
    public function fields()
    {
        $fields = parent::fields();
        
        unset(
            $fields['user_id'],
            $fields['admin_id'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
        
        return $fields;
    }

    //获取学分记录
    public static function createData($user_id, $page, $limit)
    {
        return self::find()
        ->where(['user_id' => $user_id, 'status' => self::STATUS_ACTIVE])
        ->offset($page * $limit)
        ->limit($limit)
        ->orderBy('created_at DESC')
        ->all();
    }

    //判断是否为可操作人
    public static function isOperation($user_role, $account_id, $user)
    {
        if($user_role == 'teacher'){
            $supervisor = Classes::findOne($user->class_id)->supervisor;
            $item_name =  CodeAdmin::findOne($account_id)->auths->item_name;
            $pid = substr($item_name, -3);
            if($pid == '001' || $supervisor == $account_id) {
                return true;
            }
        }
        return false;
    }
}