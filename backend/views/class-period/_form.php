<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\ClassPeriod;
use components\Datetime;

/* @var $this yii\web\View */
/* @var $model common\models\ClassPeriod */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="class-period-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'started_at')->widget(Datetime::className(),[
        'options' => [
            'datepicker' => false,
            'format' => 'H:i',
            'defaultSelect' => false,
            'allowTimes' => ClassPeriod::getTimeList()
        ]
    ]);
    ?>

    <?= $form->field($model, 'dismissed_at')->widget(Datetime::className(),[
        'options' => [
            'datepicker' => false,
            'format' => 'H:i',
            'defaultSelect' => false,
            'allowTimes' => ClassPeriod::getTimeList()
        ]
    ]);
    ?>

    <?= $form->field($model, 'position')->textInput() ?>
    
    <?php
    /*
    <?= $form->field($model, 'studio_id')->textInput() ?>
    
    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>
    */
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
