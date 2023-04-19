<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'teacher\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'teacher\modules\v2\Module'
        ]
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\Admin',
            'enableSession' => false,
            'loginUrl' => null,
            'idParam' => '__api',
        ],
        'authManager' => [   
             'class' => 'components\DbManager',    
             'itemTable' => 'auth_item',
             'assignmentTable' => 'auth_assignment',
             'itemChildTable' => 'auth_item_child',
        ],
        'urlManager' => [
            'suffix' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/admin']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/userhomework']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/classperiod']],
            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $message = Yii::$app->request->get('message');
                $error = Yii::$app->request->get('error');
                if ($response->data !== null && !empty($message)) {
                    $response->data = [
                        'success' => ($error) ? false : $response->isSuccessful,
                        'data' => $response->data,
                        'error' => ($error) ? $error : 0,
                        'message' => $message
                    ];
                    $response->statusCode = 200;
                }
            },
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
    'params' => $params,
];