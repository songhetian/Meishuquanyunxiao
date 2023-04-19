<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use components\Oss;
use backend\models\Rbac;
use common\models\Gather;
use common\grid\EnumColumn;
use common\models\Format;
use components\Datetime;
use common\models\Campus;
use backend\models\Admin;
use common\models\Activity;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-home"></i> <?= 活动 ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= 活动 ?>
                </li>
                <li class="active">
                    <?= Html::a(活动管理, ['index']) ?>
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
                    <?php if(Yii::$app->user->can(Yii::$app->controller->id)) : ?>
                        <?= Html::a(('创建活动'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                </p>

                <?php Pjax::begin(); ?>    
                        <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            //'id',
                            'title',
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'type',
                                'filter' => Activity::getValues('type'),
                                'enum' => Activity::getValues('type')
                            ],
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_top',
                                'filter' => Activity::getValues('is_top'),
                                'enum' => Activity::getValues('is_top')
                            ],
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'turn_type',
                                'filter' => Activity::getValues('turn_type'),
                                'enum' => Activity::getValues('turn_type')
                            ],
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
           
                            //'admin_id',
                            //'created_at',
                            [
                                'attribute' => 'created_at',
                                'value' => function ($model) {
                                    return date('Y/m/d H:i:s', $model->created_at);
                                },
                                'filter' => Datetime::widget([ 
                                    'name' => Format::getModelName($searchModel->className()).'[created_at]', 
                                    'options' => [
                                        'lang' => 'zh',
                                        'timepicker' => false,
                                        'format' => 'Y/m/d',
                                    ]
                                ]),
                            ],
                            [
                                'attribute' => 'updated_at',
                                'value' => function ($model) {
                                    return date('Y/m/d H:i:s', $model->updated_at);
                                },
                                'filter' => Datetime::widget([ 
                                    'name' => Format::getModelName($searchModel->className()).'[created_at]', 
                                    'options' => [
                                        'lang' => 'zh',
                                        'timepicker' => false,
                                        'format' => 'Y/m/d',
                                    ]
                                ]),
                            ],

                            ['class' => 'yii\grid\ActionColumn',
                              'header' => '操作',
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