<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Admin;
use backend\models\Rbac;
use common\models\Query;
use common\models\Keyword;
use common\models\Video;
use common\models\Campus;
use common\models\Format;
use common\grid\EnumColumn;
use components\Oss;
use components\Datetime;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\VideoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-camera"></i> <?= Yii::t('backend', 'Videos') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Material Library Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Videos'), ['index']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create Video'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                </p>

                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
                            /*
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'source',
                                'filter' => Video::getValues('source'),
                                'enum' => Video::getValues('source')
                            ],
                            */
                            'name',
                            //'studio_id',
                            //'instructor',
                            /*
                            [
                                'label' => '分类',
                                'attribute' => 'category_name',
                                'value' => 'categorys.name',
                            ],
                            
                            [
                               'attribute' => 'keyword_id',  
                               'value' => function ($model) {
                                  return ($model->keyword_id) ? Query::concatValue(Keyword::className(), $model->keyword_id) : $model->keyword_id;
                                }
                            ],
                            */
                            [
                               'attribute' => 'preview',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    $studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
                                    return ($model->preview) ? Html::img(
                                        ($model->source == $model::SOURCE_LOCAL) ? Oss::getUrl($studio, 'video', 'preview', $model->preview).$size : $model->preview.$size
                                    ) : $model->preview;
                                }
                            ],
                            // 'cc_id',
                            'watch_count',
                            [
                                'label' => '上传者',
                                'attribute' => 'admin_name',
                                'value' => 'admins.name',
                            ],
                            /*
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_public',
                                'filter' => Video::getValues('is_public'),
                                'enum' => Video::getValues('is_public')
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
                            /*
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'status',
                                'filter' => Video::getValues('status'),
                                'enum' => Video::getValues('status')
                            ],
                            */
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