<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Keyword;
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
      <?= Yii::t('backend', 'View Picture') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'Material Library Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Pictures'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Picture') ?>
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
                    /*
                    [
                        'attribute' => 'source',
                        'value' => Picture::getValues('source', $model->source)
                    ],
                    */
                    'name',
                    //'metis_material_id',
                    //'publishing_company',
                    [
                        'label' => '分类',
                        'attribute' => 'categorys.name'
                    ],
                    [
                        'attribute' => 'keyword_id',
                        'value' => ($model->keyword_id) ? Query::concatValue(Keyword::className(), $model->keyword_id, true) : $model->keyword_id
                    ],
                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => ($model->image) ? Html::img(
                            ($model->source == $model::SOURCE_LOCAL) ? Oss::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id, 'picture', 'image', $model->image).Yii::$app->params['oss']['Size']['250x250'] : $model->image.Yii::$app->params['oss']['Size']['250x250']
                        ) : $model->image, 
                    ],
                    'watch_count',
                    [
                        'label' => '上传者',
                        'attribute' => 'admins.name'
                    ],
                    /*
                    [
                        'attribute' => 'is_public',
                        'value' => Picture::getValues('is_public', $model->is_public)
                    ],
                    */
                    'created_at:datetime',
                    'updated_at:datetime',
                    //'status',
                ],
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>