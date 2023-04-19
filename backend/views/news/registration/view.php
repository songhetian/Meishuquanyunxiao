<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Keyword;
use common\models\NewList;
use common\models\Picture;
use common\models\Campus;
use common\models\Query;
use components\Oss;

/* @var $this yii\web\View */
/* @var $model common\models\Picture */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-camera" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View New') ?>
    </h2>
    <div class="breadcrumbs">
    </div>
  </div>
  <div class="main">
    <div class="row">
      <div class="col-md-6 view_width">
        <section class="tile color transparent-black">
          <div class="tile-body">
            <p>
                  <?= Html::a(Yii::t('backend', 'Update'), ['registration-info'], ['class' => 'btn btn-primary']) ?>

                <?= Html::a(Yii::t('backend', 'Back'), ['registration'], ['class' => 'btn btn-danger']) ?>
            </p>
                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'address',
            'lat',
            'lng',
            [
                'attribute' => 'pic',
                'format' => 'raw',
                'value' => ($model->pic) ? Html::img(
                     Oss::getUrl($model->studio_id, 'registration', 'pic', $model->pic).Yii::$app->params['oss']['Size']['250x250']
                ) : $model->pic, 
            ],
            'phone_number'
        ],
    ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>


