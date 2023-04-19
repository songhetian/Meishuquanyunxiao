<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\MessageCategory;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Message */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="message-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'message_category_id')->widget(
        Select2::classname(), 
        [  
            'data' => MessageCategory::getMessageCategoryList(),
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>
    
    <?= $form->field($model, 'campus_id')->widget(
        Select2::classname(), 
        [ 
            'data' => Campus::getCampusList(),
            'toggleAllSettings' => Yii::$app->params['select']['toggleAllSettings'],
            'options' => [
                'multiple' => true,
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getClass()'
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>
    <?php
    /*
    <?= $form->field($model, 'category_id')->widget(
        Select2::classname(), 
        [  
            'data' => Category::getCategoryList(),
            'toggleAllSettings' => Yii::$app->params['select']['toggleAllSettings'],
            'options' => [
                'multiple' => true,
                'placeholder' => Yii::t('common', 'Prompt')
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>
    
    <?= $form->field($model, 'class_id')->widget(
        Select2::classname(), 
        [  
            'data' => (empty($model->campus_id)) ? [] : Classes::getClassesList($model->campus_id),
            'toggleAllSettings' => Yii::$app->params['select']['toggleAllSettings'],
            'options' => [
                'multiple' => true,
                'placeholder' => Yii::t('common', 'Prompt')
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>
    */
    ?>
    
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <?php
    /*
        <?= $form->field($model, 'user_id')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'correlated_id')->textInput() ?>

        <?= $form->field($model, 'code')->textInput() ?>

        <?= $form->field($model, 'admin_id')->textInput() ?>

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
    function getClass(){
        campus_id = $('#message-campus_id').val();
        if(campus_id){
            url = "<?= Yii::$app->urlManager->createUrl(['message/get-class']) ?>";
            $.get(url, {campus_id : String(campus_id)}, function(data){
                $('#message-class_id').html(data);
            });
        }else{
            $('#message-class_id').html('');
        }
    }
</script>