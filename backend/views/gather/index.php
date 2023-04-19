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

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-home"></i> <?= Yii::t('backend', 'Cloud') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Course Material Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Cloud'), ['index']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create Cloud'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            [
                              'label'  => '分类',
                              'attribute' => 'category_id',
                              'value' => function($model) {
                                 return $model->categorys->name;
                              }       
                            ],
                            [
                              'attribute' => 'course_material_id',
                              'format'=>'raw',
                              'value' => function($model) {
                               return  Html::a(Gather::concatMaterial($model->course_material_id), ['list','id'=>$model->id], ['target'=> '_blank','data-pjax'=>0]);
                              },
                            ],
                            [
                               'attribute' => 'image',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    $studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
                                    return Html::img(
                                       Oss::getUrl($studio, 'picture', 'image', $model->image).$size
                                    );
                                }
                            ],
                            [
                              'label'  => '有效时间(年)',
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