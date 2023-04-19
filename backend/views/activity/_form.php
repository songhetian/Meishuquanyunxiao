<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\Activity;

/* @var $this yii\web\View */
/* @var $model common\models\Activity */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="buy-record-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => Activity::getValues('type'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>
    <?= $form->field($model, 'turn_type')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => Activity::getValues('turn_type'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>
    <?= $form->field($model, 'is_top')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => Activity::getValues('is_top'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>

    <?= $form->field($model, 'image')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
  
     <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'turn_id')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php $this->beginBlock('change') ?>
    $(function(){
        if($("#activity-type").val() == 10) {
            $(".field-activity-url").hide();
        }else{
            $(".field-activity-turn_type").hide();
            $(".field-activity-turn_id").hide();
        }
        if($("#activity-turn_type").val() == 1) {
            $(".field-activity-turn_id").hide();
        }

        $("#activity-type").change(function(){
            if($(this).val() == 10) {
                $(".field-activity-url").hide();
                $(".field-activity-turn_type").show();
                $(".field-activity-turn_id").show();
            }else{
                $(".field-activity-turn_type").hide();
                $(".field-activity-turn_id").hide();
                 $(".field-activity-url").show();
            }
        });

        $("#activity-turn_type").change(function(){
            if($(this).val() == 1) {
                $(".field-activity-turn_id").hide();
            }else{
                $(".field-activity-turn_id").show();
            }
        });

    })

<?php $this->endBlock() ?>  
<?php $this->registerJs($this->blocks['change']); ?>  
