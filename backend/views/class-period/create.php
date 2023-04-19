<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ClassPeriod */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-pencil" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'Create Period') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'Class Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Class Periods'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'Create Period') ?>
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