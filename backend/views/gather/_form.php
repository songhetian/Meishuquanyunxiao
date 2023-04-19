<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Admin;
use common\models\Gather;
use common\models\Category;
use common\models\CourseMaterial;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Gather */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="gather-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

       <?= $form->field($model, 'category_id')->widget(
        Select2::classname(), 
        [  
            'data' => Category::getCategoryList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>


    <?= $form->field($model, 'is_public')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => Gather::getValues("is_public"), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>
    <?= $form->field($model, 'course_material_id')->widget(
        Select2::classname(), 
        [  
            'data' => CourseMaterial::getCourseMaterialList(), 
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

    <?= $form->field($model, 'activetime')->textInput() ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'phone_number')->textInput() ?>
    
    <?= $form->field($model, 'author')->widget(
        Select2::classname(), 
        [
            'data' => Admin::getAdminList(),
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>
    <?= $form->field($model, 'image')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <?= $form->field($model, 'introduction')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script type="text/javascript">
    // function getClass(){
    //     campus_id = $('#gather-course_material_id').val();
    //     if(campus_id){
    //         url = "<?= Yii::$app->urlManager->createUrl(['admin/get-class']) ?>";
    //         $.get(url, {campus_id : String(campus_id)}, function(data){
    //             $('#gather-course_material_id').html(data);
    //         });
    //     }else{
    //         $('#gather-course_material_id').html('');
    //     }
    // }
</script>
