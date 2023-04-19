<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace developer\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'assets/css/amazeui.min.css',
        'assets/css/admin.css',
        'assets/css/app.css'
    ];
    public $js = [
    	'assets/js/amazeui.min.js',
    	'assets/js/app.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}