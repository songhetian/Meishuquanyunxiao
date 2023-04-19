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
        <h2><i class="fa fa-user"></i> 报名列表</h2> 
        <div class="breadcrumbs"> 
            <ol class="breadcrumb"> 
                <li>当前位置</li>   
                <li class="active">
                    报名
                </li> 
                <li class="active">
                    报名列表
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
                </p>
                <?php Pjax::begin(); ?>    
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'label' => '姓名',
                                'value' => 'user.name',
                            ],
                            [
                                'label' => '手机号',
                                'value' => 'user.phone_number',
                            ],
                            [
                                'label' => '身份证号',
                                'value' => 'user.national_id',
                            ],
                            [
                                'label' => '所在高中',
                                'value' => 'user.school_name',
                            ],
                            [
                                'label' => '所在年级',
                                'value' => 'user.grade',
                            ],
                            [
                                'label' => '高考年份',
                                'value' => 'user.graduation_at',
                            ],
                            [
                                'attribute' => 'timer',
                                'value' => function ($model) {
                                    return date('Y/m/d H:i:s', $model->timer);
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
                        ],
                    ]); ?>
                <?php Pjax::end(); ?>
              </div>
            </section>
          </div>
        </div> 
    </div>
</div>



