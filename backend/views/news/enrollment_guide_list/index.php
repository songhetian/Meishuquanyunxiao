<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use backend\models\Admin;
use common\grid\EnumColumn;
use common\models\EnrollmentGuideList;
use common\models\Campus;
use common\models\Format;
use components\Oss;
use components\Datetime; 

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AdminSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-user"></i> <?= Yii::t('backend', 'EnrollmentGuide List') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>   
                <li class="active">
                    <?= Yii::t('backend', 'EnrollmentGuide Management') ?>
                </li> 
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'EnrollmentGuide List'), ['enrollment-guide-list']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create EnrollmentGuide'), ['create-enrollment-guide-list'], ['class' => 'btn btn-success']) ?>
                </p>
                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'enrollment_guide_list_id',
                            'name',
                            [
                                'label' => '封面',
                               'attribute' => 'thumbnails',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    $studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
                                    return ($model->thumbnails) ? Html::img(
                                    Oss::getUrl($studio, 'enrollment', 'thumbnails', $model->thumbnails).$size
                                    ) : $model->thumbnails;
                                }
                            ],
                            [
                                'label' => '创建者',
                                'attribute' => 'admin_name',
                                'value' => 'admins.name',
                            ],
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
                            [                        
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_banner',
                                'filter' => EnrollmentGuideList::getValues('is_banner'),
                                'enum' => EnrollmentGuideList::getValues('is_banner')
                            ],
                            [                        
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_top',
                                'filter' => EnrollmentGuideList::getValues('is_top'),
                                'enum' => EnrollmentGuideList::getValues('is_top')
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'headerOptions' => ['width' => '7%'],
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Yii::$app->request->hostInfo."/news/view-enrollment-guide-list.html?enrollment_guide_list_id=".$model->enrollment_guide_list_id, [
                                            'title' => Yii::t('yii', 'View'),
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'update' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Yii::$app->request->hostInfo."/news/update-enrollment-guide-list.html?enrollment_guide_list_id=".$model->enrollment_guide_list_id, [
                                            'title' => Yii::t('yii', 'Update'),
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'delete' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', Yii::$app->request->hostInfo."/news/del-enrollment-guide-list.html?enrollment_guide_list_id=".$model->enrollment_guide_list_id, [
                                            'title' => Yii::t('yii', 'Delete'),
                                            'data-confirm' => Yii::t('yii', '确定删除?'),
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                        ]);
                                    }
                                ],
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



