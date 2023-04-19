<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Rbac;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model backend\models\Rbac */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rbac-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
        $roles[] = Yii::t('backend', 'No Parent');
        $roles += Rbac::getRoles();
    ?>
    <?= $form->field($model, 'pid')->widget(
        Select2::classname(), 
        [  
            'data' => $roles,
            'options' => [
                'placeholder' => Yii::t('backend', 'No Parent')
            ]
        ]);
    ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'permission')->widget(
        Select2::classname(), 
        [
            'data' => $model->getPermissions(),
            'toggleAllSettings' => Yii::$app->params['select']['toggleAllSettings'],
            'options' => [
                'multiple' => true,
                'placeholder' => Yii::t('common', 'Prompt'),
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>

    <?php
    /*
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'studio_id')->textInput() ?>
    
    <?= $form->field($model, 'type')->textInput() ?>
    
    <?= $form->field($model, 'scope')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rule_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data')->textarea(['rows' => 6]) ?>

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
