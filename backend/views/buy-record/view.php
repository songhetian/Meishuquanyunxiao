<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Campus */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-home" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= '查看云课件订单' ?>
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
          <?= Html::a('生成云课件订单', ['index']) ?>
        </li> 
        <li class="active">
          <?= '查看云课件订单' ?>
        </li>
      </ol>
    </div>
  </div>
  <div class="main">
    <div class="row">
      <div class="col-md-6 view_width">
        <section class="tile color transparent-black">
          <div class="tile-body">
            <p>
                <?php if(Yii::$app->user->can(Yii::$app->controller->id . '/update')) : ?>
                  <?= Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?php endif; ?>
                <?= Html::a(Yii::t('backend', 'Back'), ['index'], ['class' => 'btn btn-danger']) ?>
            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'buy_id',
                    'buy_studio',
                    'gather_id',
                    'gather_studio',
                    'created_at:datetime',
                    'updated_at:datetime',
                    'active_at:datetime',
                    'price',
                ],
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>