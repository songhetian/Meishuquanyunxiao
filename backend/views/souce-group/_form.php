<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\SouceGroup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="souce-group-form">

    <?php $form = ActiveForm::begin(); ?>

    <div style="display:none;">
        <?= $form->field($model, 'type')->hiddenInput(['value' => Yii::$app->session->get('type')]) ?>
    </div>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
    /*
    <?= $form->field($model, 'admin_id')->textInput() ?>

    <?= $form->field($model, 'material_library_id')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'is_public')->textInput() ?>

    <?= $form->field($model, 'type')->textInput() ?>

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