<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\CourseMaterial;

/* @var $this yii\web\View */
/* @var $model common\models\CourseMaterial */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-book" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Material') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          系统管理
        </li>
        <li class="active">
          常见问题
        </li> 
        <li class="active">
          展示
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
                <?php if(!$gather_id): ?>
                  <?= Html::a(Yii::t('backend', 'Back'), ['index'], ['class' => 'btn btn-danger']) ?>
                <?php else: ?>
                  <?= Html::a(Yii::t('backend', 'Back'), ['gather/list','id'=>$gather_id], ['class' => 'btn btn-danger']) ?>
                <?php endif; ?>
            </p>
          <?= DetailView::widget([
              'model' => $model,
              'attributes' => [
                  'id',
                  'title',
                  'info:html',
                  'created_at:datetime',
              ],
          ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>