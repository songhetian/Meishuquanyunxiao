<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use components\Oss;
use common\models\Category;
use common\models\Keyword;
use common\models\SouceGroup;

/* @var $this yii\web\View */
/* @var $model common\models\Picture */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="picture-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    
    <?= $form->field($model, 'group')->widget(
        Select2::classname(), 
        [  
            'data' => SouceGroup::getGroupList(SouceGroup::TYPE_PICTURE, Yii::$app->session->get('gid'))
        ]);
    ?>
    <?php
    /*
    <?= $form->field($model, 'category_id')->widget(
        Select2::classname(), 
        [  
            'data' => Category::getCategoryChildList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getKeyword()'
            ]
        ]);
    ?>
    */
    ?>
    <?php
    /*
    <?= $form->field($model, 'keyword_id')->widget(
        Select2::classname(), 
        [  
            'data' => Keyword::getKeywordList($model->category_id), 
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
    
    <? if($model->isNewRecord) : ?>
        <?= $form->field($model, 'image[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
    <? endif ?>
    
    <?php
    /*
    <?= $form->field($model, 'source')->textInput() ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'metis_material_id')->textInput() ?>
    
    <?= $form->field($model, 'publishing_company')->textInput() ?>

    <?= $form->field($model, 'watch_count')->textInput() ?>
    
    <?= $form->field($model, 'admin_id')->textInput() ?>

    <?= $form->field($model, 'is_public')->textInput() ?>

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
    function getKeyword(){
        category_id = $('#picture-category_id').val();
        url = "<?= Yii::$app->urlManager->createUrl(['picture/get-keyword']) ?>";
        $.get(url, {category_id : category_id}, function(data){
            $('#picture-keyword_id').html(data);
        });
    }
</script>
