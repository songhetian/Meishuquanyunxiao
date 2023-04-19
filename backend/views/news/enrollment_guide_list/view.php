<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Keyword;
use common\models\EnrollmentGuideList;
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
      <?= Yii::t('backend', 'View EnrollmentGuide') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'EnrollmentGuide Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'EnrollmentGuide List'), ['enrollment-guide-list']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View EnrollmentGuide') ?>
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
                  <?= Html::a(Yii::t('backend', 'Update'), ['update-enrollment-guide-list', 'enrollment_guide_list_id' => $model->enrollment_guide_list_id], ['class' => 'btn btn-primary']) ?>

                <?= Html::a(Yii::t('backend', 'Back'), ['enrollment-guide-list'], ['class' => 'btn btn-danger']) ?>
            </p>
                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'enrollment_guide_list_id',
            'name',
            'url:url',
            [
                'attribute' => 'thumbnails',
                'format' => 'raw',
                'value' => ($model->thumbnails) ? Html::img(
                     Oss::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id, 'enrollment', 'thumbnails', $model->thumbnails).Yii::$app->params['oss']['Size']['250x250']
                ) : $model->thumbnails, 
            ],
            'desc:html',
            [
                'attribute' => 'status',
                'value' => EnrollmentGuideList::getValues('status', $model->status)
            ],
            [
                'attribute' => 'is_banner',
                'value' => EnrollmentGuideList::getValues('is_banner', $model->is_banner)
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


