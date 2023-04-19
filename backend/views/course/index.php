<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Rbac;
use common\models\Format;
use components\Datetime;
use common\grid\EnumColumn;
use common\models\Course;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\CourseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div id="content" class="col-md-12">
    <div class="pageheader"> 
        <h2><i class="fa fa-pencil"></i> <?= Yii::t('backend', 'Courses') ?> </h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>
                <li class="active">
                  <?= Yii::t('backend', 'Class Management') ?>
                </li>
                <li class="active">
                    <?= Html::a(Yii::t('backend', 'Courses'), ['index']) ?>
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
                        <?= Html::a(Yii::t('backend', 'Create Course'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?php endif; ?>
                    <?php if(Yii::$app->user->can(Yii::$app->controller->id.'/export')): ?>
                     <?= Html::a(Yii::t('backend', 'Export Course'), ['export'], ['class' => 'btn btn-success']) ?>
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
                                'label' => '上课时间',
                                'attribute' => 'class_period_name',
                                'value' => 'classPeriods.name',
                                
                            ],
                            [
                                'label' => '所属班级',
                                'attribute' => 'class_name',
                                'value' => 'classes.name',
                            ],
                            [
                                'label' => '科目',
                                'attribute' => 'category_name',
                                'value' => 'categorys.name',
                            ],
                            [
                                'label' => '教学老师',
                                'attribute' => 'instructor_name',
                                'value' => 'instructors.name',
                            ],
                            [
                                'label' => '教学形式',
                                'attribute' => 'instruction_method_name',
                                'value' => 'instructionMethods.name',
                                
                            ],
                            'class_content:ntext',
                            [
                                'label' => '课件',
                                'attribute' => 'course_material_name',
                                'value' => 'courseMaterials.name',
                                
                            ],
                            // 'course_material_id',
                            [
                                'attribute' => 'started_at',
                                'value' => function ($model) {
                                    return date('Y/m/d', $model->started_at);
                                },
                                'filter' => Datetime::widget([ 
                                    'name' => Format::getModelName($searchModel->className()).'[started_at]', 
                                    'options' => [
                                        'lang' => 'zh',
                                        'timepicker' => false,
                                        'format' => 'Y/m/d',
                                    ]
                                ]),
                            ],
                            /*
                            [
                                'attribute' => 'ended_at',
                                'value' => function ($model) {
                                    return date('Y/m/d', $model->ended_at);
                                },
                                'filter' => Datetime::widget([ 
                                    'name' => Format::getModelName($searchModel->className()).'[ended_at]', 
                                    'options' => [
                                        'lang' => 'zh',
                                        'timepicker' => false,
                                        'format' => 'Y/m/d',
                                    ]
                                ]),
                            ],
                            */
                            // 'class_emphasis:ntext',
                            // 'note:ntext',
                            [
                                'label' => '上传者',
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
                                'attribute' => 'status',
                                'filter' => Course::getValues('status'),
                                'enum' => Course::getValues('status')
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