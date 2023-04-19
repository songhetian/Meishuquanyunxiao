<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use backend\models\Admin;
use common\grid\EnumColumn;
use common\models\NewList;
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
        <h2><i class="fa fa-user"></i> <?= Yii::t('backend', 'New List') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>   
                <li class="active">
                    <?= Yii::t('backend', 'New Management') ?>
                </li> 
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'New List'), ['new-list']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create New'), ['create-new-list'], ['class' => 'btn btn-success']) ?>
                </p>
                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'new_list_id',
                            'name',
                            [
                                'label' => '封面',
                               'attribute' => 'thumbnails',
                               'format' => 'raw',
                               'value' => function($model) {
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    $studio = Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id;
                                    return ($model->thumbnails) ? Html::img(
                                    Oss::getUrl($studio, 'new', 'thumbnails', $model->thumbnails).$size
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
                                'attribute' => 'is_top',
                                'filter' => NewList::getValues('is_top'),
                                'enum' => NewList::getValues('is_top')
                            ],
                            [                        
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_banner',
                                'filter' => NewList::getValues('is_banner'),
                                'enum' => NewList::getValues('is_banner')
                            ],

                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'template' => ' {up} {view} {update} {delete}',
                                'headerOptions' => ['width' => '11%'],
                                'buttons' => [
                                     'up' => function ($url, $model) {
                                        return Html::a('<span name="'.$model->new_list_id.'">置顶</span>',"javascript:void(0);", [
                                            'title' => Yii::t('app', '置顶'),
                                        ]);
                                    },
                                    'view' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Yii::$app->request->hostInfo."/news/view-new-list.html?new_list_id=".$model->new_list_id, [
                                            'title' => Yii::t('yii', 'View'),
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'update' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Yii::$app->request->hostInfo."/news/update-new-list.html?new_list_id=".$model->new_list_id, [
                                            'title' => Yii::t('yii', 'Update'),
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'delete' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', Yii::$app->request->hostInfo."/news/del-new-list.html?new_list_id=".$model->new_list_id.'&get='.json_encode(Yii::$app->request->queryParams), [
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

<script src="http://backend.meishuquanyunxiao.com/assets/js/jquery.js"></script>
<script type="text/javascript">
  $("*[title='置顶']").click(function(){
    $.ajax({
      type:"GET",
      url:"/news/new-listupp.html",
      async:false,
      data:{id:$(this).children("span").attr("name")},
      success:function(m){
        history.go(0);
      }
    })
  })
</script>

