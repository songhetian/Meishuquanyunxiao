<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Category;
use common\models\Gather;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-home" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Cloud') ?>
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
          <?= Html::a(Yii::t('backend', 'Cloud'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Cloud') ?>
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
                        'id',
                        'name',
                        [
                          'attribute' => 'category_id',
                          'value' => function($model) {
                             return $model->categorys->name;
                          }       
                        ],
                        [
                          'attribute' => 'course_material_id',
                          'value' => function($model) {
                             return Gather::concatMaterial($model->course_material_id);
                          }       
                        ],
                        [
                          'label'  => '有效时间(月)',
                          'attribute' => 'activetime',
                          'value' => function($model) {
                             return $model->activetime;
                          }
                        ],
                        [
                          'attribute' => 'price',
                          'value' => function($model) {
                             return number_format($model->price,2,".","");
                          }
                        ],
                       [       
                            'label'=>'作者',
                            'attribute' => 'author',
                            'value' => function($model) {
                                   return $model->authors->name; 
                               }
                        ],
                        'admin_id',
                        'phone_number',
                        [
                            'attribute' => 'created_at',
                            'value' => function ($model) {
                                return date('Y/m/d H:i:s', $model->created_at);
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function ($model) {
                                return date('Y/m/d H:i:s', $model->updated_at);
                            }
                        ],
                        'introduction:ntext',
                        //'status',
                    ],
                ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>