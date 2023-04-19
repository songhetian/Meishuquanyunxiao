<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\CourseMaterial;
use components\Oss;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\UserHomework */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-homework-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'user_id')->widget(
        Select2::classname(), 
        [  
            'data' => User::getUserList(),
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'disabled' => 'disabled'
            ]
        ]);
    ?>

    <?= $form->field($model, 'course_material_id')->widget(
        Select2::classname(), 
        [  
            'data' => CourseMaterial::getCourseMaterialList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'disabled' => 'disabled'
            ]
        ]);
    ?>
    
    <?php
    /*
    <?= $form->field($model, 'image')->hiddenInput() ?>
    */
    ?>

    <?= $form->field($model, 'comments')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'score')->textInput() ?>

    <?php
    /*
        <?= $form->field($model, 'evaluator')->textInput() ?>

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
