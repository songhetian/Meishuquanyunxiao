<?php

use developer\assets\LoginAsset;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

LoginAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?= $name.Html::encode(Yii::$app->name) ?></title>
  <meta name="description" content="<?= $name.Html::encode(Yii::$app->name) ?>">
  <meta name="keywords" content="<?= $name.Html::encode(Yii::$app->name) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="renderer" content="webkit">
  <meta http-equiv="Cache-Control" content="no-siteapp" />
  <?= Html::csrfMetaTags() ?>
  <?php $this->head() ?>
      <style type="text/css">
      .help-block{font-size: 12px;color: #FFF;}
    </style>

</head>

<body data-type="login">
  <?php $this->beginBody() ?>
  <div class="am-g myapp-login">
  <div class="myapp-login-logo-block  tpl-login-max">
    <div class="myapp-login-logo-text">
      <div class="myapp-login-logo-text">
        美术圈<span> 云校</span> <i class="am-icon-skyatlas"></i> 
      </div>
    </div>
    <div class="am-u-sm-10 login-am-center">
      <?php $form = ActiveForm::begin([
            'class' => 'am-form',
            'fieldConfig' => [
              'template' => '{input}{error}'
            ]
      ]); ?>
        <fieldset>
          <div class="am-form-group">
            <?= $form->field($model, 'phone_number')->textInput([
                'id' => 'doc-ipt-email-1',
                'autofocus' => true,
                'placeholder' => '请输入管理员账号'
            ]) ?>
          </div>
          <div class="am-form-group">
            <?= $form->field($model, 'password')->passwordInput([
                'id' => 'doc-ipt-pwd-1',
                'placeholder' => '请输入管理员密码'
            ]) ?>
          </div>
          <p><?= Html::submitButton(Yii::t('backend', 'Login'), ['class' => 'am-btn am-btn-default']) ?></p>
        </fieldset>
      <?php ActiveForm::end(); ?>
    </div>
  </div>
  </div>
  <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>