<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PagingAsset extends AssetBundle
{
	public $css = [
        // Bootstrap
        'assets/css/paging/paging.css'
    ];

    public $js = [
    	'assets/js/paging/query.js',
    	'assets/js/paging/paging.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}