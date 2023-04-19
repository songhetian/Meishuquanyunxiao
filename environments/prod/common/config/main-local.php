<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-2ze9ucm92kq0ouh58.mysql.rds.aliyuncs.com;dbname=meishuquanyunxiao',
            'username' => 'msqyx',
            'password' => 'Meishuquanyunxiao2016',
            'charset' => 'utf8mb4',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
