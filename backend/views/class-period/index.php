<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ClassPeriodSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-pencil"></i> <?= Yii::t('backend', 'Class Periods') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Class Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Class Periods'), ['index']) ?>
                </li>
            </ol>
        </div> 
    </div> 
    <div class="main">
        <div class="row">
          <div class="col-md-12">
            <section class="tile color transparent-black">
              <div class="tile-body color transparent-black rounded-corners">
                <p>
                    <?php if(Yii::$app->user->can(Yii::$app->controller->id . '/create')) : ?>
                        <?= Html::a(Yii::t('backend', 'Create Period'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                </p>

                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
                            'name',
                            //'studio_id',
                            'started_at',
                            'dismissed_at',
                            'position',
                            // 'created_at',
                            // 'updated_at',
                            // 'status',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'template' => Rbac::getTemplate(Yii::$app->controller->id),
                                'headerOptions' => ['width' => '7%'],
                            ],
                        ],
                    ]); ?>
                <?php Pjax::end(); ?>
              </div>
            </section>
          </div>
        </div> 
    </div>
</div>