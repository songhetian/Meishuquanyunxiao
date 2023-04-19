<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Classes */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-home" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Classes') ?>
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
          <?= Html::a(Yii::t('backend', 'Classes'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Classes') ?>
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
                    //'id',
                    'name',
                    'year',
                    [
                        'label' => '所属校区',
                        'attribute' => 'campuses.name'
                    ],
                    [
                        'label' => '班主任',
                        'attribute' => 'supervisors.name'
                    ],
                    'note:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                    // 'status',
                ],
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>