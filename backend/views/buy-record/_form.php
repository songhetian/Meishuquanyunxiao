<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\Campus;
use common\models\BuyRecord;
use common\models\Gather;
use common\models\Studio;
/* @var $this yii\web\View */
/* @var $model common\models\BuyRecord */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="buy-record-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->textInput(['readonly'=> $model->isNewRecord ? false : true]) ?>

    <?= $form->field($model, 'buy_studio')->widget(
        Select2::classname(), 
        [  
            'data' => Studio::getList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>

    <?php if($model->isNewRecord):?>
        <?= $form->field($model, 'role')->widget(
            Select2::classname(), 
            [
                'hideSearch' => 'true',
                'data' => BuyRecord::getValues('role'), 
                'options' => [
                    'placeholder' => Yii::t('common', 'Prompt'),
                ]
            ]);
        ?>
    <?php else: ?>
        <?= $form->field($model, 'role')->textInput(['readonly'=> $model->isNewRecord ? false : true]) ?>
        
    <?php endif;?>
    <?= $form->field($model, 'gather_id')->widget(
        Select2::classname(), 
        [  
            'data' => BuyRecord::getGatherList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
            ]
        ]);
    ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton('生成', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
