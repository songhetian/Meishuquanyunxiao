<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Rbac;

/* @var $this yii\web\View */
/* @var $model backend\models\Rbac */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-cog" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Role') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'System Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Roles'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Role') ?>
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
                <?= Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->name], ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('backend', 'Back'), ['index'], ['class' => 'btn btn-danger']) ?>
            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'name',
                    [
                        'attribute' => 'pid',
                        'value' => ($model->pid) ? Rbac::findOne(['name' => $model->pid])->description : Yii::t('backend', 'No Parent')
                    ],
                    // 'studio_id',
                    // 'type',
                    // 'scope',
                    'description:ntext',
                    // 'rule_name',
                    // 'data:ntext',
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