<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use components\Oss;
/* @var $this yii\web\View */
/* @var $model common\models\Campus */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campus-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>


<div class="form-group field-ebook-status">
<label class="control-label" for="ebook-status">经纬度( 点击下方按钮，查询到本校地址，复制页面右上角经纬度粘贴在此 )</label>
<input type="text" id="registration-name" class="form-control" name="latLng" value="<?php if(!empty($model->lat)){ echo $model->lat.','.$model->lng;}  ?>" maxlength="255"/>
<br/>
<a class="btn btn-success" href="http://api.map.baidu.com/lbsapi/getpoint/index.html" target="_blank" >查询经纬度</a>
<div class="help-block"></div>
</div>
        <?= $form->field($model, 'pic')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
        
            <?php
        if(!empty($model->pic)){
            echo "<img src='".OSS::getUrl($model->studio_id,'registration','pic',$model->pic).Yii::$app->params['oss']['Size']['original']."' width='200' height='200'/>";
        }
    ?>
    <br/>
     <br/>
      <br/>
    <?= $form->field($model, 'phone_number')->textInput() ?>
    <?php
    /*
    <?= $form->field($model, 'studio_id')->textInput() ?>

    <?= $form->field($model, 'is_main')->textInput() ?>
    
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