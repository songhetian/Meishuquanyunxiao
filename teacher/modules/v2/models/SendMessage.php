<?php

namespace teacher\modules\v2\models;

class SendMessage
{
    public static function sendSuccessMsg($message)
    {
        return [
        	'success' => true,
            'message' => $message,
            'error'   => 0
        ];
    }

    public static function sendErrorMsg($message)
    {
    	return [
    		'success' => false,
    		'message' => $message,
            'error'   => 0
    	];
    }
    public static function sendVerifyErrorMsg1($model)
    {
        $errors = $model->getErrors();
        $data = [];
        foreach ($errors as $key => $error) {
            $data[$key] .= end($error);
        }
        return [
            'success' => false,
            'message' => implode(',',$data),
            'error'   => 0
        ];
    }

    public static function sendVerifyErrorMsg($model, $message)
    {
    	$errors = $model->getErrors();
        $data = [];
        foreach ($errors as $key => $error) {
            $data[$key] = end($error);
        }

        $data = implode('', $data);
        return [
            'success' => false,
            'message' => $data,
            'error'   => 0
        ];
    }
}