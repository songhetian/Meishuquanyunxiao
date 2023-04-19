<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\PrizeList;
use common\models\NewList;
use components\Oss;
use common\models\Campus;
use common\models\Classes;
use teacher\modules\v1\models\Admin;
use common\models\Race;
use common\models\City;
use common\models\Format;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
    
        <?= $form->field($model, 'thumbnails')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
        
            <?php
        if(!empty($model->thumbnails)){
            echo "<img src='".OSS::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id,'prize','thumbnails',$model->thumbnails).Yii::$app->params['oss']['Size']['original']."' width='200' height='200'/>";
        }
    ?>
    <br/>


<label class="control-label" for="prizelist-thumbnails">内容</label>
        <?= \crazydouble\ueditor\UEditor::widget([
            'model' => $model,
            'attribute' => 'desc',
            'config' => [
               'toolbars' => Yii::$app->params['ueditor']['toolbars']
            ]
        ]) ?>
    <?= $form->field($model, 'is_banner')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => PrizeList::getValues('is_banner'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
