<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Video */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-book" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'Update Material') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'Course Material Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Course Materials'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'Update Material') ?>
        </li>
      </ol>
    </div>
  </div>
  <div class="main">
    <div class="row">
      <?= $this->render('_form', [
            'model' => $model,
      ]) ?>
    </div>
  </div>
</div>