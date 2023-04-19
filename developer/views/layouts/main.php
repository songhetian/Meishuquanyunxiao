<?php 

use developer\assets\AppAsset; 
use yii\helpers\Html; 
use backend\models\Menu;
use common\models\Format;
AppAsset::register($this); 

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
  
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="<?= Yii::$app->charset ?>" />
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->name) ?></title>
    <?php $this->head() ?>
    <style type="text/css">
      div.required label:after {content: "*"; color: red;margin-left: 5px;}
    </style>
  </head>
  
  <body class="bg-3">
    <?php $this->beginBody() ?>
    <div id="wrap">
      <div class="row">
        <!-- 固定导航 -->
        <div class="navbar navbar-default navbar-fixed-top navbar-transparent-black mm-fixed-top" role="navigation" id="navbar">
          <!-- Logo -->
          <div class="navbar-header col-md-2">
            <a class="navbar-brand" href="<?= Yii::$app->homeUrl ?>">
              <strong>
                <?= Html::encode(Yii::$app->name) ?>
              </strong>
            </a>
            <div class="sidebar-collapse">
              <?= Html::a('<i class="fa fa-bars"></i>', 'javascript:void(0);') ?>
            </div>
          </div>
          <!-- /Logo -->
          <div class="navbar-collapse">
            <!-- 页面刷新 -->
            <ul class="nav navbar-nav refresh">
              <li class="divided">
                <?= Html::a(
                  '<i class="fa fa-refresh"></i>',
                  'javascript:void(0);',
                  ['class' => 'page-refresh']
                ) ?>
              </li>
            </ul>
            <!-- 快捷操作 -->
            <ul class="nav navbar-nav quick-actions" >
                <li class="dropdown divided user" id="current-user">
                  <div class="profile-photo">
                    <img src="<?= Yii::$app->request->baseUrl; ?>/assets/images/profile-photo.jpg"/>
                  </div>
                  <?= Html::a(
                    Yii::$app->user->identity->name,
                    'javascript:void(0);',
                    ['class' => 'dropdown-toggle options', 'data-toggle' => 'dropdown']
                  ) ?>
                <li>
                  <?= Html::a(
                    '<i class="fa fa-power-off"></i>',
                    Yii::$app->urlManager->createUrl(['site/logout']),
                    ['onclick' => "return confirm('您确定要退出系统吗？')"]
                  ) ?>
                </li>
            </ul>
            <!-- /快捷操作 -->
            <!-- 侧边栏 -->
            <ul class="nav navbar-nav side-nav" id="sidebar">
              <li class="collapsed-content">
                <ul>
                  <li class="search"></li>
                </ul>
              </li>
              <li class="navigation" id="navigation">
                <?= Html::a(
                  '导航栏 <i class="fa fa-angle-up"></i>',
                  'javascript:void(0);',
                  ['class' => 'sidebar-toggle', 'data-toggle' => '#navigation']
                )?>
                <?= Menu::menu(); ?>
              </li>
            </ul>
            <!-- /侧边栏 -->
          </div>
        </div>
        <!-- /固定导航-->
        <!-- 页面主内容 -->
        <?= $content ?>
        <!-- /页面主内容 -->
      </div>
    </div>
    <?php $this->endBody() ?>
  </body>
  <?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>
</html>
<?php $this->endPage() ?>