<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\grid\EnumColumn;
use common\models\Classes;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ClassesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-home"></i> <?= Yii::t('backend', 'Classes') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Campus Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Classes'), ['index']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create Classes'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            'year',
                            [
                                'label' => '所属校区',
                                'attribute' => 'campus_name',
                                'value' => 'campuses.name',
                            ],
                            [
                                'label' => '班主任',
                                'attribute' => 'supervisor_name',
                                'value' => 'supervisors.name', 
                            ],
                            // 'note:ntext',
                            // 'created_at',
                            // 'updated_at',
                            // 'status',
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'status',
                                'filter' => Classes::getValues('status'),
                                'enum' => Classes::getValues('status')
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'template' => Rbac::getTemplate(Yii::$app->controller->id),
                                'headerOptions' => ['width' => '7%'],
                                'buttons' => [
                                    'delete' => function ($url, $model, $key) {
                                        if($model->status == $model::STATUS_ACTIVE){
                                                return Html::a(
                                                  "<span class='glyphicon glyphicon-trash'></span>",
                                                  ['delete', 'id' => $key], 
                                                  ['title'=>'删除', 'aria-label' => '删除', 'data-confirm' => '您确定要删除此项么？', 'data-method' => 'post', 'data-pjax' => 0]
                                                );
                                            }else{
                                                if(Yii::$app->user->can(Yii::$app->controller->id.'/recovery')){
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