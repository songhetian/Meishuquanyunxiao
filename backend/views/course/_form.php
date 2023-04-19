<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Admin;
use common\models\ClassPeriod;
use common\models\Classes;
use common\models\Category;
use common\models\InstructionMethod;
use common\models\CourseMaterial;
use common\models\Course;
use components\Datetime;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Course */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="course-form">

    <?php $form = ActiveForm::begin(); ?>

    <div style="display:none;">
        <?= $form->field($model, 'id')->hiddenInput() ?>
    </div>
    
    <?= $form->field($model, 'class_period_id')->widget(
        Select2::classname(), 
        [  
            'data' => ClassPeriod::getClassPeriodList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getStartedAt()'
            ]
        ]);
    ?>

    <?= $form->field($model, 'class_id')->widget(
        Select2::classname(), 
        [  
            'data' => Classes::getClassesList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getStartedAt()',
            ]
        ]);
    ?>

    <?= $form->field($model, 'category_id')->widget(
        Select2::classname(), 
        [  
            'data' => Category::getCategoryList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>

    <?= $form->field($model, 'instructor')->widget(
        Select2::classname(), 
        [
            'data' => Admin::getAdminList(),
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>

    <?= $form->field($model, 'instruction_method_id')->widget(
        Select2::classname(), 
        [  
            'data' => InstructionMethod::getInstructionMethodList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'course_material_id')->widget(
        Select2::classname(), 
        [  
            'data' => CourseMaterial::getCourseMaterialList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <div class="datetime" style="display:none;">
        <?= $form->field($model, 'started_at')->widget(Datetime::className(),[
            'options' => [
                'lang' => 'zh',
                'format' => 'Y/m/d',
                'yearStart' => '2000',
                'yearEnd' => '2030',
                'timepicker' => false,
                'inline' => true,
                'scrollMonth' => false,
                'scrollInput' => false,
                'defaultSelect' => false,
                'disabledDates' => Course::getDisabledDates($model->class_period_id, $model->class_id, $model->id),
                'onchange' => 'getEndedAt()'
            ]
        ]);
        ?>
        
        <?= $form->field($model, 'ended_at')->widget(Datetime::className(),[
            'options' => [
                'lang' => 'zh',
                'format' => 'Y/m/d',
                'yearStart' => '2000',
                'yearEnd' => '2030',
                'timepicker' => false,
                'inline' => true,
                'scrollMonth' => false,
                'scrollInput' => false,
                'defaultSelect' => false,
                'minDate' => $model->started_at,
                'maxDate' => Course::getMaxDate($model->class_period_id, $model->class_id, $model->started_at)
            ]
        ]);
        ?>
    </div>

    <?= $form->field($model, 'class_content')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'class_emphasis')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?php
    /*
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
    function getStartedAt(){
        class_period_id = $('#course-class_period_id').val();
        class_id = $('#course-class_id').val();
        id = $('#course-id').val();
        url = "<?= Yii::$app->urlManager->createUrl(['course/get-started-at']) ?>";
        if(class_period_id && class_id){
            $.get(url, {class_period_id : class_period_id, class_id : class_id, id : id}, function(data){
                jQuery('#course-started_at').datetimepicker({
                    disabledDates : data
                });
                action = "<?= Yii::$app->controller->module->requestedAction->id ?>";
                if(action == 'create'){
                    $('#course-started_at').val('');
                    $('#course-ended_at').val('');
                }
                $('.datetime').show();
            });
        }
    }

    function getEndedAt(){
        started_at = $('#course-started_at').val();
        class_period_id = $('#course-class_period_id').val();
        class_id = $('#course-class_id').val();
        id = $('#course-id').val();
        url = "<?= Yii::$app->urlManager->createUrl(['course/get-ended-at']) ?>";
        if(class_period_id && class_id && started_at){
            $.get(url, {class_period_id : class_period_id, class_id : class_id, started_at : started_at, id : id}, function(data){
                jQuery('#course-ended_at').datetimepicker({
                    minDate: data.mindate,
                    maxDate: data.maxDate
                });
                action = "<?= Yii::$app->controller->module->requestedAction->id ?>";
                if(action == 'create'){
                    $('#course-ended_at').val('');
                }
            });
        }
    }
    <?php $this->beginBlock('js_end') ?>
        action = "<?= Yii::$app->controller->module->requestedAction->id ?>";
        if(action == 'update'){
            //更新加载数据
            getStartedAt();
            getEndedAt();
        }
    <?php $this->endBlock() ?>
</script>