<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Keyword;
use common\models\WorksList;
use common\models\Picture;
use common\models\Campus;
use common\models\Query;
use components\Oss;

?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-camera" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Works') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'Works Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Works List'), ['works-list']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Works') ?>
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

                  <?= Html::a(Yii::t('backend', 'Update'), ['update-works-list', 'works_list_id' => $model->works_list_id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('backend', 'Back'), ['works-list'], ['class' => 'btn btn-danger']) ?>
            </p>
                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'works_list_id',
            'name',
            [
                'attribute' => 'type',
                'value' => WorksList::getValues('type', $model->type)
            ],
            [
                'attribute' => 'pic_url',
                'format' => 'raw',
                'value' => ($model->pic_url) ? Html::img(
                     Oss::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id, 'works', 'pic_url', $model->pic_url).Yii::$app->params['oss']['Size']['250x250']
                ) : $model->pic_url, 
            ],
            'desc:html',
            [
                'attribute' => 'is_teacher',
                'value' => WorksList::getValues('is_teacher', $model->is_teacher)
            ],
            [
                'attribute' => 'status',
                'value' => WorksList::getValues('status', $model->status)
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
