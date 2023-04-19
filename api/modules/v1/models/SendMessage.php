<?php

namespace api\modules\v1\models;

class SendMessage
{
    public static function sendSuccessMsg($message)
    {
        return [
        	'success' => true,
            'message' => $message
        ];
    }

    public static function sendErrorMsg($message)
    {
    	return [
    		'success' => false,
    		'message' => $message
    	];
    }

    public static function sendVerifyErrorMsg($model, $message)
    {
    	$errors = $model->getErrors();
        $data = [];
        if($errors){
            foreach ($errors as $key => $error) {
                $data[$key] .= end($error);
            }
        }
        return [
            'success' => false,
            'data' => $data,
            'message' => $message
        ];
    }
}