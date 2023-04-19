<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\grid\EnumColumn;
use common\models\User;
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
        <h2><i class="fa fa-user"></i> <?= Yii::t('backend', 'Users') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>   
                <li class="active">
                    <?= Yii::t('backend', 'User Management') ?>
                </li> 
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Users'), ['index']) ?>
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
                    <?php if(Yii::$app->user->can(Yii::$app->controller->id.'/export')): ?>
                     <?= Html::a(Yii::t('backend', 'Export User'), ['export'], ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                </p>

                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                               'attribute' => 'image',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['57x57'];
                                    $image = ($model->image) ? Oss::getUrl($model->studio_id, 'picture', 'image', $model->image) : "http://meishuquanyunxiao.img-cn-beijing.aliyuncs.com/icon/touxiang.png";
                                    return Html::img($image.$size);
                                }
                            ],
                            [
                                'label' => '姓名',
                                'attribute' => 'name',
                                'value' => function ($model) {
                                    return ($model->name) ? $model->name : '无名称';
                                },
                            ],
                            [
                                'label' => '所在班级',
                                'attribute' => 'class_name',
                                'value' => 'classes.name',
                            ],
                            'credit',
                            [
                                'label' => '激活码',
                                'attribute' => 'code_name',
                                'value' => 'codes.code',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'template' => Rbac::getTemplate(Yii::$app->controller->id),
                                'headerOptions' => ['width' => '6%'],
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