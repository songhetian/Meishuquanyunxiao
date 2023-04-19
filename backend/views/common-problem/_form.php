<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CommonProblem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="common-problem-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <!-- <?= $form->field($model, 'info')->textarea(['rows' => 6]) ?> -->

    <?= \crazydouble\ueditor\UEditor::widget([
            'model' => $model,
            'attribute' => 'info',
            'config' => [
               'toolbars' => Yii::$app->params['ueditor']['toolbars']
            ]
        ]) ?>
    <div class="form-group" style="margin-top: 20px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
