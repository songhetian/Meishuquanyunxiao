<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\models\User;
use common\models\Campus;
use common\models\Format;
use components\Oss;
use components\Datetime;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserHomeworkSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-user"></i> <?= Yii::t('backend', 'User Homeworks') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'User Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'User Homeworks'), ['index']) ?>
                </li>
            </ol>
        </div> 
    </div> 
    <div class="main">
        <div class="row">
          <div class="col-md-12">
            <section class="tile color transparent-black">
              <div class="tile-body color transparent-black rounded-corners">
                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
                            [
                                'label' => '班级',
                                'attribute' => 'class_name',
                                'value' => 'users.classes.name',
                            ],
                            [
                                'label' => '学生',
                                'attribute' => 'user_name',
                                'value' => 'users.name',
                            ],
                            [
                                'label' => '关联教案',
                                'attribute' => 'course_material_name',
                                'value' => 'courseMaterials.name',
                                
                            ],
                            [
                               'label' => '作品',
                               'attribute' => 'image',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    $studio = Campus::findOne(User::findOne($model->user_id)->campus_id)->studio_id;
                                    return ($model->image) ? Html::img(
                                        Oss::getUrl($studio, 'user-homework', 'image', $model->image).$size
                                    ) : $model->image;
                                }
                            ],
                            [
                                'label' => '点评讲师',
                                'attribute' => 'evaluator_name',
                                'value' => 'evaluators.name', 
                            ],
                            'comments:ntext',
                            'score',
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
                                    'name' => Format::getModelName($searchModel->className()).'[updated_at]', 
                                    'options' => [
                                        'lang' => 'zh',
                                        'timepicker' => false,
                                        'format' => 'Y/m/d',
                                    ]
                                ]),
                            ],
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