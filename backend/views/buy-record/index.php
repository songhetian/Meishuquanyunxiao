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
use common\models\BuyRecord;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-home"></i> <?= '生成云课件订单' ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Course Material Management') ?>
                </li>
                <li class="active">
                    <?= Html::a('生成云课件订单', ['index']) ?>
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
                        <?= Html::a('生成云课件订单', ['create'], ['class' => 'btn btn-success']) ?>
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
                                'label' => '购买人',
                                'attribute' => 'buy_id',
                                'value' => function($model) {
                                    if($model->role == 10){
                                        return $model->admins->name;
                                    }elseif($model->role == 20){
                                        return $model->students->name;
                                    }
                                }
                                
                            ],
                            [
                                'label' => '购买身份',
                                'attribute' => 'role',
                                'value' => function ($model) {
                                    return BuyRecord::getValues('role')[$model->role];
                                },
                                
                            ],
                            [
                                'label' => '课件包名',
                                'attribute' => 'gather_id',
                                'value' => 'gathers.name',
                                
                            ],
                            //'gather_studio',
                            [
                                'label' => '购买画室',
                                'attribute' => 'gather_studio',
                                'value' => 'studios.name',
                                
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => function ($model) {
                                    return date('Y/m/d', $model->created_at);
                                },
                            ],
                            //'updated_at',
                            [
                                'attribute' => 'active_at',
                                'value' => function ($model) {
                                    return date('Y/m/d', $model->active_at);
                                },
                            ],
                            'price',
                            //'status',

                            ['class' => 'yii\grid\ActionColumn'],
                        ],
                    ]); ?>
                <?php Pjax::end(); ?>
              </div>
            </section>
          </div>
        </div> 
    </div>
</div>