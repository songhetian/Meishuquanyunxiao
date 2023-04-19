<?php

namespace backend\models;

use Yii;
use yii\base\Model;

class InitializeForm extends Model
{
    public $review_num;
    public $api_name;
    public $backend_name;
    
    public $dbname;
    public $username;
    public $password;
    public $host;

    public $bucket;
    public $app_key;
    public $master_secret;

    public function rules()
    {
        return [
            [['review_num', 'api_name', 'backend_name', 'dbname', 'username', 'password', 'host', 'bucket', 'app_key', 'master_secret'], 'required'],
            ['review_num', 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'review_num' => '购买数量',
            'api_name' => 'APP名称',
            'backend_name' => '网站名称',

            'dbname' => '数据库名称',
            'username' => '用户名',
            'password' => '密码',
            'host' => '数据库主机',

            'bucket' => 'Bucket',
            'app_key' => 'App Key',
            'master_secret' => 'Master Secret'
        ];
    }
}
