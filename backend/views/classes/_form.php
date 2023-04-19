<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Admin;
use common\models\Campus;
use common\models\Classes;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Classes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="classes-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'campus_id')->widget(
        Select2::classname(), 
        [  
            'data' => Campus::getCampusList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getAdmin()'
            ]
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->widget(
        Select2::classname(), 
        [  
            'data' => Classes::getYearList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'supervisor')->widget(
        Select2::classname(), 
        [  
            'data' => (empty($model->campus_id)) ? [] : Admin::getAdminList($model->campus_id), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?php
    /*
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
<script type="text/javascript">
    function getAdmin(){
        campus_id = $('#classes-campus_id').val();
        if(campus_id){
            url = "<?= Yii::$app->urlManager->createUrl(['classes/get-admin']) ?>";
            $.get(url, {campus_id : campus_id}, function(data){
                $('#classes-supervisor').html(data);
            });
        }else{
            $('#classes-supervisor').html('');
        }
    }
</script>