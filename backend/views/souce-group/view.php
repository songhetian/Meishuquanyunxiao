<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\SouceGroup;

/* @var $this yii\web\View */
/* @var $model common\models\SouceGroup */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-camera" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View SouceGroup') ?>
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
          <?= Html::a(Yii::t('backend', 'SouceGroups'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View SouceGroup') ?>
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
                <?= Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('backend', 'Back'), ['index'], ['class' => 'btn btn-danger']) ?>
            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    /*
                    [
                        'label' => '上传者',
                        'attribute' => 'admins.name'
                    ],
                    */
                    //'role',
                    //'material_library_id:ntext',
                    //'is_main',
                    [
                        'attribute' => 'is_public',
                        'value' => SouceGroup::getValues('is_public', $model->is_public)
                    ],
                    //'type',
                    'created_at:datetime',
                    'updated_at:datetime',
                    [
                        'attribute' => 'status',
                        'value' => SouceGroup::getValues('status', $model->status)
                    ],
                ],
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>