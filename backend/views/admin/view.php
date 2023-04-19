<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Query;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-home" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Admin') ?>
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
          <?= Html::a(Yii::t('backend', 'Admins'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Admin') ?>
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
                    'role',
                    [
                        'attribute' => 'campus_id',
                        'value' => ($model->campus_id) ? Query::concatValue(Campus::className(), $model->campus_id, true) : $model->campus_id
                    ],
                    [
                        'attribute' => 'category_id',
                        'value' => ($model->category_id) ? Query::concatValue(Category::className(), $model->category_id, true) : $model->category_id
                    ],
                    [
                        'attribute' => 'class_id',
                        'value' => ($model->class_id) ? Query::concatValue(Classes::className(), $model->class_id, true) : $model->class_id
                    ],
                    'phone_number',
                    'name',
                    'created_at:datetime',
                    'updated_at:datetime',
                    [
                        'attribute' => 'is_all_visible',
                        'value' => Admin::getValues('is_all_visible', $model->is_all_visible)
                    ],
                    //'is_main',
                    //'auth_key',
                    //'password_hash',
                    //'password_reset_token',
                    // 'status',
                ],
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>