<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\models\CourseMaterial;
use common\grid\EnumColumn;
use common\models\Format;
use components\Datetime;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CourseMaterialSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-book"></i> <?= Yii::t('backend', 'Course Materials') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Course Material Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Course Materials'), ['index']) ?>
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
                        <?php if($gather_id): ?>
                            <?= Html::a(Yii::t('backend', 'Create Material'), ['create','gather_id'=>$gather_id], ['class' => 'btn btn-success']) ?>
                        <?php else: ?>
                            <?= Html::a(Yii::t('backend', 'Create Material'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?php endif; ?>
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
                                'label' => '上传者',
                                'attribute' => 'admin_name',
                                'value' => 'admins.name',
                            ],
                            /*
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_public',
                                'filter' => CourseMaterial::getValues('is_public'),
                                'enum' => CourseMaterial::getValues('is_public')
                            ],
                            */
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
                                'class' => EnumColumn::className(),
                                'attribute' => 'status',
                                'filter' => CourseMaterial::getValues('status'),
                                'enum' => CourseMaterial::getValues('status')
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'template' => Rbac::getTemplate(Yii::$app->controller->id),
                                'headerOptions' => ['width' => '7%'],
                                'buttons' => [
                                    'delete' => function ($url, $model, $key) {
                                        if($model->id != Yii::$app->user->identity->id){
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