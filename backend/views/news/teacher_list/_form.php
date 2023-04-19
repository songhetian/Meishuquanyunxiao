<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\TeacherList;
use components\Oss;
use common\models\Campus;
use common\models\Classes;
use teacher\modules\v1\models\Admin;
use common\models\Race;
use common\models\City;
use common\models\Format;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\models\TeacherList */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="teacher-list-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>


    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'auth')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'pic_url')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
        
            <?php
        if(!empty($model->pic_url)){
            echo "<img src='".OSS::getUrl(Campus::findOne(Admin::findOne($model->admin_id)->campus_id)->studio_id,'teacher','pic_url',$model->pic_url).Yii::$app->params['oss']['Size']['original']."' width='200' height='200'/>";
        }
    ?>
    <br/>


    <label class="control-label" for="prizelist-thumbnails">简介</label>
        <?= \crazydouble\ueditor\UEditor::widget([
            'model' => $model,
            'attribute' => 'desc',
            'config' => [
               'toolbars' => Yii::$app->params['ueditor']['toolbars']
            ]
        ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
