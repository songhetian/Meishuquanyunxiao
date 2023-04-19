<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
?>

<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-home" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'Update Admin') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'Campus Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Admins'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'Update Admin') ?>
        </li>
      </ol>
    </div>
  </div>
  <div class="main">
    <div class="row">
      <div class="col-md-6">
        <section class="tile color transparent-black">
          <div class="tile-body">
          	<?= $this->render('_form', [
                  'model' => $model,
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>