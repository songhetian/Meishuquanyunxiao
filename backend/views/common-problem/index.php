<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\grid\EnumColumn;
use common\models\Format;
use components\Datetime;
use common\models\CommonProblem
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-home"></i> 常见问题 </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                 常见问题
                </li>
                <li class="active">
                    <!-- <?= Html::a(Yii::t('backend', 'Cloud'), ['index']) ?> -->

                    常见问题
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
                    <?= Html::a("添加常见问题", ['create'], ['class' => 'btn btn-success']) ?>
                </p>
                <?php Pjax::begin(); ?>    
                        <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            //'id',
                            'title',
                            'info:html',
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
                                'class' => EnumColumn::className(),
                                'attribute' => 'status',
                                'filter' => CommonProblem::getValues('status'),
                                'enum'   => CommonProblem::getValues('status')
                            ],
                            // [
                            //     'attribute' => 'updated_at',
                            //     'value' => function ($model) {
                            //         return date('Y/m/d H:i:s', $model->updated_at);
                            //     },
                            //     'filter' => Datetime::widget([ 
                            //         'name' => Format::getModelName($searchModel->className()).'[created_at]', 
                            //         'options' => [
                            //             'lang' => 'zh',
                            //             'timepicker' => false,
                            //             'format' => 'Y/m/d',
                            //         ]
                            //     ]),
                            // ],

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