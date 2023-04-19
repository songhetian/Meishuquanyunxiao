<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'name' => ' - 管理系统',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'user' => [
            'identityClass' => 'backend\models\Admin',
            'enableAutoLogin' => true,
            'enableSession' => true,
            'idParam' => '__backend',
        ],
        'authManager' => [   
             'class' => 'components\DbManager',    
             'itemTable' => 'auth_item',
             'assignmentTable' => 'auth_assignment',
             'itemChildTable' => 'auth_item_child',
        ],
        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'controllerMap' => [
        'ueditor' => [
            'class' => 'crazydouble\ueditor\UEditorController',
            //'zoom' => ['width' => 500, 'height' => 500], //缩放，默认不缩放
            'config' => [
                //server config @see http://fex-team.github.io/ueditor/#server-config
                'imagePathFormat' => '/assets/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
                'scrawlPathFormat' => '/assets/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
                'snapscreenPathFormat' => '/assets/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
                'catcherPathFormat' => '/assets/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
                'videoPathFormat' => '/assets/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}',
                'filePathFormat' => '/assets/upload/file/{yyyy}{mm}{dd}/{rand:4}_{filename}',
                'imageManagerListPath' => '/assets/upload/image/',
                'imageCompressEnable' => false,
                'fileManagerListPath' => '/assets/upload/file/',
            ]
        ]
    ],
    'params' => $params,
];