<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\models\Format;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Query;
use common\grid\EnumColumn;
use components\Datetime;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-cog"></i> <?= Yii::t('backend', 'Messages') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'System Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Messages'), ['index']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create Message'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                </p>
                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
                            [
                                'label' => '消息类型',
                                'attribute' => 'message_category_name',
                                'value' => 'messageCategorys.name',
                            ],
                            [
                               'attribute' => 'campus_id',  
                               'value' => function ($model) {
                                  return ($model->campus_id) ? Query::concatValue(Campus::className(), $model->campus_id) : $model->campus_id;
                                }
                            ],
                            /*
                            [
                               'attribute' => 'category_id',  
                               'value' => function ($model) {
                                  return ($model->category_id) ? Query::concatValue(Category::className(), $model->category_id) : $model->category_id;
                                }
                            ],
                            [
                               'attribute' => 'class_id',  
                               'value' => function ($model) {
                                  return ($model->class_id) ? Query::concatValue(Classes::className(), $model->class_id) : $model->class_id;
                                }
                            ],
                            */
                            [
                                'label' => '用户',
                                'attribute' => 'user_name',
                                'value' => 'users.name',
                            ],
                            'title',
                            'content:ntext',
                            //'correlated_id',
                            //'code',
                            [
                                'label' => '发布者',
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