<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Course */
?>
<div id="content" class="col-md-12">
  <div class="pageheader">
    <h2>
      <i class="fa fa-pencil" style="line-height: 48px;padding-left: 1px;">
      </i>
      <?= Yii::t('backend', 'View Course') ?>
    </h2>
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li>
          当前位置
        </li>
        <li class="active">
          <?= Yii::t('backend', 'Class Management') ?>
        </li>
        <li class="active">
          <?= Html::a(Yii::t('backend', 'Courses'), ['index']) ?>
        </li> 
        <li class="active">
          <?= Yii::t('backend', 'View Course') ?>
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
                        'label' => '上课时间',
                        'attribute' => 'classPeriods.name'
                    ],
                    [
                        'label' => '所属班级',
                        'attribute' => 'classes.name'
                    ],
                    [
                        'label' => '科目',
                        'attribute' => 'categorys.name'
                    ],
                    [
                        'label' => '教学老师',
                        'attribute' => 'instructors.name'
                    ],
                    [
                        'label' => '教学形式',
                        'attribute' => 'instructionMethods.name'
                    ],
                    [
                        'label' => '关联教案',
                        'attribute' => 'courseMaterials.name'
                    ],
                    'course_material_id',
                    'started_at:datetime',
                    'ended_at:datetime',
                    'class_content:ntext',
                    'class_emphasis:ntext',
                    'note:ntext',
                    [
                        'label' => '上传者',
                        'attribute' => 'admins.name'
                    ],
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