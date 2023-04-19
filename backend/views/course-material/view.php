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
          <?= Yii::t('backend', 'Course Material Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Course Materials'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Material') ?>
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
                    //'id',
                    'name',
                    'description:html',
                    [
                        'label' => '上传者',
                        'attribute' => 'admins.name'
                    ],
                    /*
                    [
                        'attribute' => 'is_public',
                        'value' => CourseMaterial::getValues('is_public', $model->is_public)
                    ],
                    */
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