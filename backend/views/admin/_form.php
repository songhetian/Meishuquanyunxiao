<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Rbac;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Format;
use kartik\select2\Select2;
use backend\models\Admin;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="admin-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'role')->widget(
        Select2::classname(), 
        [  
            'data' => Rbac::getRoles(),
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
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

    <?= $form->field($model, 'category_id')->widget(
        Select2::classname(), 
        [  
            'data' => Category::getCategoryList(),
            'toggleAllSettings' => Yii::$app->params['select']['toggleAllSettings'],
            'options' => [
                'multiple' => true,
                'placeholder' => Yii::t('common', 'All Visible')
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
                'placeholder' => Yii::t('common', 'All Visible')
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>

    <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true]) ?>

    <input name="<?= Format::getModelName($model->className()) ?>[password_hash]" type='password' style="display:none" />
    <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php if(Yii::$app->user->identity->studio_id == 183){ ?>
    <?= $form->field($model, 'is_sell')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => Admin::getValues('is_sell'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>
    <?= $form->field($model, 'sell_num')->textInput() ?>
    <?php   } ?>
    <?php
    /*
    <?= $form->field($model, 'is_all_visible')->textInput() ?>

    <?= $form->field($model, 'is_main')->textInput() ?>
    
    <?= $form->field($model, 'auth_key')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password_reset_token')->textInput(['maxlength' => true]) ?>

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
        campus_id = $('#admin-campus_id').val();
        if(campus_id){
            url = "<?= Yii::$app->urlManager->createUrl(['admin/get-class']) ?>";
            $.get(url, {campus_id : String(campus_id)}, function(data){
                $('#admin-class_id').html(data);
            });
        }else{
            $('#admin-class_id').html('');
        }
    }
</script>