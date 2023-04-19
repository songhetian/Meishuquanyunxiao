<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\User;
use common\models\Campus;
use components\Oss;

/* @var $this yii\web\View */
/* @var $model common\models\UserHomework */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-user" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Homework') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'User Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'User Homeworks'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Homework') ?>
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
                    [
                        'label' => '用户',
                        'attribute' => 'users.name'
                    ],
                    [
                        'label' => '关联教案',
                        'attribute' => 'courseMaterials.name'
                    ],
                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => ($model->image) ? Html::img(
                            Oss::getUrl(Campus::findOne(User::findOne($model->user_id)->campus_id)->studio_id, 'user-homework', 'image', $model->image).Yii::$app->params['oss']['Size']['1000x1000'],
                            ['width' => 250, 'height' => 250, 'class' => 'img']
                        ) : $model->image,
                    ],
                    [
                        'label' => '点评讲师',
                        'attribute' => 'evaluators.name'
                    ],
                    'comments:ntext',
                    'score',
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
<script type="text/javascript">
   <?php $this->beginBlock('js_end') ?>
    $('.img').click(function(){
      url = $(this).attr('src');
      window.open(url);
    });
   <?php $this->endBlock() ?>
</script>