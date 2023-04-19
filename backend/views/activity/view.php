<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Admin;
use common\models\Category;
use common\models\Gather;
use components\Oss;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-home" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= 活动 ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= 活动 ?>
        </li>
        <li class="active">
          <?= Html::a(活动, ['index']) ?>
        </li> 
        <li class="active">
          <?= 查看活动 ?>
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
                        [
                            'attribute' => 'type',
                            'value' => function ($model) {
                                return $model->type == 10 ? '内部跳转' : '外部跳转';
                            }
                        ],
                        [
                            'attribute' => 'is_top',
                            'value' => function ($model) {
                                return $model->is_top == 10 ? '置顶' : '非置顶';
                            }
                        ],
                        'title',
                            [
                               'label'  => '活动图',
                               'attribute' => 'image',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    $studio = $model->studio_id;
                                    return Html::img(
                                       Oss::getUrl($studio, 'picture', 'image', $model->image).$size
                                    );
                                }
                            ],
                        'url',
   
                        [
                            'attribute' => 'created_at',
                            'value' => function ($model) {
                                return date('Y/m/d', $model->created_at);
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function ($model) {
                                return date('Y/m/d', $model->updated_at);
                            }
                        ],
                        #'introduction:ntext',
                        //'status',
                    ],
                ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>