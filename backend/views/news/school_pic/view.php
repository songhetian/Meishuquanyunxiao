<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Keyword;
use common\models\SchoolPic;
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
      <?= Yii::t('backend', 'View School') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'School Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'School List'), ['school-list']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View School') ?>
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

                  <?= Html::a(Yii::t('backend', 'Update'), ['update-school-pic', 'school_pic_id' => $model->school_pic_id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('backend', 'Back'), ['school-pic'], ['class' => 'btn btn-danger']) ?>
            </p>
                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'school_pic_id',
            [
                'attribute' => 'type',
                'value' => SchoolPic::getValues('type', $model->type)
            ],
            [
                'attribute' => 'pic_url',
                'format' => 'raw',
                'value' => ($model->pic_url) ? Html::img(
                     Oss::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id, 'school', 'pic_url', $model->pic_url).Yii::$app->params['oss']['Size']['250x250']
                ) : $model->pic_url, 
            ],
            'desc:html',
            [
                'attribute' => 'status',
                'value' => SchoolPic::getValues('status', $model->status)
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
