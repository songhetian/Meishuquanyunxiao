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
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'New Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'New List'), ['new-list']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View New') ?>
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
                  <?= Html::a(Yii::t('backend', 'Update'), ['update-new-list', 'new_list_id' => $model->new_list_id], ['class' => 'btn btn-primary']) ?>

                <?= Html::a(Yii::t('backend', 'Back'), ['new-list'], ['class' => 'btn btn-danger']) ?>
            </p>
                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'new_list_id',
            'name',
            'url:url',
            [
                'attribute' => 'thumbnails',
                'format' => 'raw',
                'value' => ($model->thumbnails) ? Html::img(
                     Oss::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id, 'new', 'thumbnails', $model->thumbnails).Yii::$app->params['oss']['Size']['250x250']
                ) : $model->thumbnails, 
            ],
            'desc:html',
            [
                'attribute' => 'status',
                'value' => NewList::getValues('status', $model->status)
            ],
            [
                'attribute' => 'is_banner',
                'value' => NewList::getValues('is_banner', $model->is_banner)
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>


