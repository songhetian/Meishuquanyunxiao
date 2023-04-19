<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\models\Format;
use common\models\SouceGroup;
use common\grid\EnumColumn;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SouceGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-cog"></i> <?= Yii::t('backend', 'SouceGroups') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Material Library Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'SouceGroups'), ['index']) ?>
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
                    <?= Html::a(Yii::t('backend', 'Create SouceGroup'), ['create'], ['class' => 'btn btn-success']) ?>
                </p>

                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
                            [
                                'attribute' => 'name',
                                'format'=>'raw',
                                'value' => function($model) {
                                    $action = ($model->type == SouceGroup::TYPE_PICTURE) ? 'picture' : 'video';
                                    return  Html::a(
                                        $model->name,
                                        [
                                            $action.'/index',
                                            'gid' => $model->id
                                        ],
                                        [
                                            'target' => '_blank',
                                            'data-pjax' => 0
                                        ]
                                    );
                                },
                            ],
                            /*
                            [
                                'label' => '上传者',
                                'attribute' => 'admin_name',
                                'value' => 'admins.name',
                            ],
                            */
                            //'role',
                            
                            // 'is_main',
                            // 'type',
                            // 'created_at',
                            // 'updated_at',
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_public',
                                'filter' => SouceGroup::getValues('is_public'),
                                'enum' => SouceGroup::getValues('is_public')
                            ],
                            /*
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'status',
                                'filter' => SouceGroup::getValues('status'),
                                'enum' =>  SouceGroup::getValues('status')
                            ],
                            */
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {update} {is-public} {delete}',
                                'headerOptions' => ['width' => '9%'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                            return Html::a(
                                              "<span class='glyphicon glyphicon-eye-open'></span>",
                                              ['view', 'id' => $key], 
                                              ['title'=>'查看', 'aria-label' => '查看', 'data-pjax' => 0]
                                            );
                                    },
                                    'update' => function ($url, $model, $key) {
                                            return Html::a(
                                              "<span class='glyphicon glyphicon-pencil'></span>",
                                              ['update', 'id' => $key], 
                                              ['title'=>'更新', 'aria-label' => '更新', 'data-pjax' => 0]
                                            );
                                    },
                                    'is-public' => function ($url, $model, $key) {
                                        if($model->is_public == $model::NOT_PUBLIC){
                                            return Html::a(
                                              "<span class='fa fa-check'></span>",
                                              ['is-public', 'id' => $key], 
                                              ['title'=>'公开', 'aria-label' => '公开', 'data-confirm' => '您确定公开该分组吗？', 'data-method' => 'post', 'data-pjax' => 0]
                                            );
                                        }else{
                                            return Html::a(
                                              "<span class='fa fa-times'></span>",
                                              ['is-public', 'id' => $key], 
                                              ['title'=>'取消公开', 'aria-label' => '取消公开', 'data-confirm' => '您确定取消公开该分组吗？', 'data-method' => 'post', 'data-pjax' => 0]
                                            );
                                        }
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        if($model->is_main == $model::NOT_PUBLIC){
                                            if($model->status == $model::STATUS_ACTIVE){
                                                return Html::a(
                                                  "<span class='glyphicon glyphicon-trash'></span>",
                                                  ['delete', 'id' => $key], 
                                                  ['title'=>'删除', 'aria-label' => '删除', 'data-confirm' => '您确定要删除此项么？', 'data-method' => 'post', 'data-pjax' => 0]
                                                );
                                            }else{
                                                return Html::a(
                                                  "<span class='glyphicon glyphicon-cloud-upload'></span>",
                                                  ['recovery', 'id' => $key], 
                                                  ['title'=>'恢复', 'aria-label' => '恢复', 'data-confirm' => '您确定要恢复此项么？', 'data-method' => 'post', 'data-pjax' => 0]
                                                );
                                            } 
                                        }
                                    },
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