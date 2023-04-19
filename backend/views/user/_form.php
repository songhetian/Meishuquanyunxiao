<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\Campus;
use common\models\Classes;
use common\models\Race;
use common\models\City;
use common\models\Format;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'campus_id')->widget(
        Select2::classname(), 
        [  
            'data' => Campus::getCampusList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange'   => 'getClasses()'
            ]
        ]);
    ?>
    <?= $form->field($model, 'class_id')->widget(
        Select2::classname(), 
        [  
            'data' => (empty($model->campus_id)) ? [] : Classes::getClassesList($model->campus_id), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'is_all_visible')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('is_all_visible'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'is_review')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('is_review'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <input name="<?= Format::getModelName($model->className()) ?>[password_hash]" type='password' style="display:none" />
    <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gender')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('gender'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'national_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'family_member_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'relationship')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('relationship'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'organization')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contact_phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'race')->widget(
        Select2::classname(), 
        [  
            'data' => Race::getRaceList(), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'student_type')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('student_type'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'career_pursuit_type')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('career_pursuit_type'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'residence_type')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('residence_type'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'grade')->widget(
        Select2::classname(), 
        [
            'hideSearch' => 'true',
            'data' => User::getValues('grade'), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ]
        ]);
    ?>

    <?= $form->field($model, 'province')->widget(
        Select2::classname(), 
        [  
            'data' => City::getCityList(0), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getCity()'
            ]
        ]);
    ?>

    <?= $form->field($model, 'city')->widget(
        Select2::classname(), 
        [  
            'data' => City::getCityList($model->province), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt')
            ],
        ]);
    ?>

    <?= $form->field($model, 'detailed_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'qq_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'school_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'united_exam_province')->widget(
        Select2::classname(), 
        [  
            'data' => City::getCityList(0), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getCity()'
            ]
        ]);
    ?>

    <?= $form->field($model, 'fine_art_instructor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'exam_participant_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sketch_score')->textInput() ?>

    <?= $form->field($model, 'color_score')->textInput() ?>

    <?= $form->field($model, 'quick_sketch_score')->textInput() ?>

    <?= $form->field($model, 'design_score')->textInput() ?>

    <?= $form->field($model, 'verbal_score')->textInput() ?>

    <?= $form->field($model, 'math_score')->textInput() ?>

    <?= $form->field($model, 'english_score')->textInput() ?>

    <?= $form->field($model, 'total_score')->textInput() ?>

    <?= $form->field($model, 'pre_school_assessment')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?php
    /*
    <?= $form->field($model, 'student_id')->textInput() ?>

    <?= $form->field($model, 'is_graduation')->textInput() ?>

    <?= $form->field($model, 'graduation_at')->textInput() ?>

    <?= $form->field($model, 'admin_id')->textInput() ?>
    
    <?= $form->field($model, 'auth_key')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'password_reset_token')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'device_token')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'access_token')->textInput(['maxlength' => true]) ?>

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
    function getCity(){
        pid = $('#user-province').find('option:selected').val();
        url = "<?= Yii::$app->urlManager->createUrl(['user/get-city']) ?>";
        if(pid){
            $.get(url, {pid : pid}, function(data){
                $('#user-city').html(data);
                city = $('#user-city > option:eq(0)').html();
                $('#select2-user-city-container').attr('title', city);
                $('#select2-user-city-container').html(city);
            });
        }
    }

    //获取班级信息
    function getClasses(){
        campus_id = $('#user-campus_id').val();
        url = "<?= Yii::$app->urlManager->createUrl(['user/classes-list']) ?>";
        $.get(url,{campus_id:campus_id}, function(data){
            $("#user-class_id").html(data);
        });
    }
</script>