<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Admin;
use backend\models\Rbac;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Query;
use common\models\Format;
use common\grid\EnumColumn;
use components\Datetime;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AdminSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-home"></i> <?= Yii::t('backend', 'Admins') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Campus Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Admins'), ['index']) ?>
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
<!--                     <?php if(Yii::$app->user->can(Yii::$app->controller->id . '/create')) : ?>
                        <?= Html::a(Yii::t('backend', 'Create Admin'), ['create'], ['class' => 'btn btn-success']) ?> -->
                    <?php endif; ?>
                    <?php if(Yii::$app->user->can(Yii::$app->controller->id.'/export')): ?>
                     <?= Html::a(Yii::t('backend', 'Export Admin'), ['export'], ['class' => 'btn btn-success']) ?>
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
                                'attribute' => 'role',
                                'value' => function ($model) {
                                    $role = Yii::$app->authManager->getRolesByUser($model->id);
                                    return $role[key($role)]->description;
                                },
                            ],
                            [
                               'attribute' => 'campus_id',  
                               'value' => function ($model) {
                                  return ($model->campus_id) ? Query::concatValue(Campus::className(), $model->campus_id) : Yii::t('backend', 'All Visible');
                                }
                            ],
                            [
                               'attribute' => 'category_id',  
                               'value' => function ($model) {
                                  return ($model->category_id) ? Query::concatValue(Category::className(), $model->category_id) : Yii::t('backend', 'All Visible');
                                }
                            ],
                            [
                               'attribute' => 'class_id',  
                               'value' => function ($model) {
                                  return ($model->class_id) ? Query::concatValue(Classes::className(), $model->class_id) : Yii::t('backend', 'All Visible');
                                }
                            ],
                            'phone_number',
                            'name',
                            // 'is_main',
                            // 'auth_key',
                            // 'password_hash',
                            // 'password_reset_token',
                            /*
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
                            */
                            // 'updated_at',
                            // 'status',
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'status',
                                'filter' => Admin::getValues('status'),
                                'enum' => Admin::getValues('status')
                            ],
                            [
                                'class' => EnumColumn::className(),
                                'attribute' => 'is_all_visible',
                                'filter' => Admin::getValues('is_all_visible'),
                                'enum' => Admin::getValues('is_all_visible')
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '操作',
                                'template' => Rbac::getTemplate(Yii::$app->controller->id, ['is-all-visible']),
                                'headerOptions' => ['width' => '9%'],
                                'buttons' => [
                                    'delete' => function ($url, $model, $key) {
                                        if($model->id != Yii::$app->user->identity->id && $model->is_main == $model::MAIN_NOT_YET){
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
                                    'is-all-visible' => function ($url, $model, $key) {
                                        if($model->is_all_visible == $model::MYSELF_VISIBLE){
                                            return Html::a(
                                              "<span class='fa fa-check'></span>",
                                              ['is-all-visible', 'id' => $key], 
                                              ['title'=>'全部可见', 'aria-label' => '全部可见', 'data-confirm' => '您确定设置为全部可见吗？', 'data-method' => 'post', 'data-pjax' => 0]
                                            );
                                        }else{
                                            return Html::a(
                                              "<span class='fa fa-times'></span>",
                                              ['is-all-visible', 'id' => $key], 
                                              ['title'=>'只看自己', 'aria-label' => '只看自己', 'data-confirm' => '您确定设置为只看自己吗？', 'data-method' => 'post', 'data-pjax' => 0]
                                            );
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
<script type="text/javascript">
    var excel = document.getElementById('excel');
    excel.onclick = function(){
        var url = window.location.href;
        var serch = window.location.search;
       
        if (serch !== '') { 
            url += '&excel=1';
        }else{
            url += '?excel=1';
        }
        window.location.href = url;
        return false;
    }
</script>