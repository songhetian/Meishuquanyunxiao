<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\User;
use common\models\City;
use components\Oss;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-user" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View User') ?>
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
          <?= Html::a(Yii::t('backend', 'Users'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View User') ?>
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
                       'attribute' => 'image',
                       'format' => 'raw',
                       'value' => ($model->image) ? Html::img(Oss::getUrl($model->studio_id, 'picture', 'image', $model->image) . Yii::$app->params['oss']['Size']['250x250']) : Html::img("http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png" . Yii::$app->params['oss']['Size']['250x250']),
                    ],

                    'credit',
                    'phone_number',
                    [
                        'attribute' => 'gender',
                        'value' => User::getValues('gender', $model->gender)
                    ],
                    [
                        'label' => '所在班级',
                        'attribute' => 'classes.name'
                    ],
                    [
                        'label' => '姓名',
                        'attribute' => 'name',
                        'value' => ($model->name) ? $model->name : '无名称'
                    ],
                    'national_id',
                    'contact_phone',
                    [
                        'label' => '省',
                        'attribute' => 'provinces.name'
                    ],
                    [
                        'label' => '市',
                        'attribute' => 'citys.name'
                    ],
                    'grade',
                    'school_name',
                    'graduation_at',
                    [
                        'label' => '激活码',
                        'attribute' => 'codes.code',
                    ],
                ],
            ]) ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>