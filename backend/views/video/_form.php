<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\assets\SparkAsset;
use common\models\Category;
use common\models\Keyword;
use components\Oss;
use components\Spark;
use kartik\select2\Select2;
use common\models\SouceGroup;

SparkAsset::register($this);

/* @var $this yii\web\View */
/* @var $model common\models\Video */
/* @var $form yii\widgets\ActiveForm */
?>
<style type="text/css">
    .field-video-cc_id { display: inline-block; width: 70%; float: left;}
    .upload { display: inline-block; float: left; margin-top: 25px; margin-left:5px;}
    #swfDiv { width: 37px; height: 35px; position:relative; z-index: 1; margin-bottom: -35px;}
</style>
<div class="video-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'group')->widget(
        Select2::classname(), 
        [  
            'data' => SouceGroup::getGroupList(SouceGroup::TYPE_VIDEO, Yii::$app->session->get('gid'))
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
    /*
    <?= $form->field($model, 'category_id')->widget(
        Select2::classname(), 
        [  
            'data' => Category::getCategoryChildList(Category::TYPE_VIDEO), 
            'options' => [
                'placeholder' => Yii::t('common', 'Prompt'),
                'onchange' => 'getKeyword()'
            ]
        ]);
    ?>

    
    <?= $form->field($model, 'keyword_id')->widget(
        Select2::classname(), 
        [  
            'data' => Keyword::getKeywordList($model->category_id, Keyword::TYPE_VIDEO), 
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
    <?= $form->field($model, 'preview')->fileInput() ?>
    
    <?= $form->field($model, 'cc_id')->textInput(['maxlength' => true, 'readonly' => '', 'style' => ['background-color' => 'rgba(255, 255, 255, 0.1)']]) ?>
    
    <div class="upload">
        <div id="swfDiv"></div>
        <span class="btn btn-primary btn-file">
          <i class="fa fa-upload"></i>
          <input type="button" value="upload" id="btn_width" style="display:none;"/>
        </span>
        <input class="btn btn-default" type="button" value="确认上传" onclick="submitvideo();">
    </div>
    <div style="clear:both;"></div>
    
    <?= \crazydouble\ueditor\UEditor::widget([
        'model' => $model,
        'attribute' => 'description',
        'config' => [
           'toolbars' => Yii::$app->params['ueditor']['toolbars']
        ]
    ]) ?>
    <div style="margin-top:10px;"></div>
    <?php
    /*
    <?= $form->field($model, 'source')->textInput() ?>

    <?= $form->field($model, 'studio_id')->textInput() ?>

    <?= $form->field($model, 'instructor')->textInput() ?>
    
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
        category_id = $('#video-category_id').val();
        url = "<?= Yii::$app->urlManager->createUrl(['video/get-keyword']) ?>";
        $.get(url, {category_id : category_id}, function(data){
            $('#video-keyword_id').html(data);
        });
    }
    
    //选中上传文件，获取文件名函数
    function on_spark_selected_file(filename) {
        var cc = $("#video-cc_id");
        cc.val('');
        cc.attr('placeholder', filename);
        cc.attr('status', 0);
        $("#video-name").attr('value', splitFileName(filename));

    }

    function splitFileName(text) {
        var pattern = /\.{1}[a-z]{1,}$/;
        if (pattern.exec(text) !== null) {
            return (text.slice(0, pattern.exec(text).index));
        } else {
            return text;
        }
    }
    //验证上传是否正常进行函数
    function on_spark_upload_validated(status, videoid) {
        if (status == "OK") {
            alert("正在上传,请耐心等待上传进度!");
            $("#video-cc_id").attr('videoid', videoid);
        } else if (status == "NETWORK_ERROR") {
            alert("网络错误");
        } else {
            alert("api错误码：" + status);
        }
    }

    //通知上传进度函数
    function on_spark_upload_progress(progress) {
        var uploadProgress = $("#video-cc_id");
        if (progress == -1) {
            uploadProgress.attr('placeholder', '上传出错');
        } else if (progress == 100) {
            videoid = uploadProgress.attr('videoid');
            uploadProgress.val(videoid);
            uploadProgress.attr('status', 10);
        } else {
            uploadProgress.val('');
            uploadProgress.attr('placeholder', "上传视频进度：" + progress + "%");
            uploadProgress.attr('status', 10);
        }
    }

    function positionUploadSWF() {
        var btn_width = $("#btn_width").css("width");
        var btn_height = $("#btn_width").css("height");
        $("#swfDiv").css({ width: btn_width, height: btn_height });
    }

    function submitvideo() {
        var videofile = $("#video-cc_id").attr('placeholder');
        var title = $("#video-name").val();
        //var tag = $('#video-category_id').val();
        var description = $("#video-name").val();
        var status = $("#video-cc_id").attr('status');
        var url = "<?= Yii::$app->urlManager->createUrl(['video/get-upload-url']) ?>";
        //if(videofile && title && tag && description && status == 0){
        if(videofile && title && description && status == 0){
            $.get(url, {title : title, description : description}, function(data){
                document.getElementById("uploadswf").start_upload(data);
            });
        }
    }

    <?php $this->beginBlock('js_end') ?> 
        // 加载上传flash ------------- start
        var swfobj = new SWFObject('http://union.bokecc.com/flash/api/uploader.swf', 'uploadswf', '37', '35', '8');
        swfobj.addVariable( "progress_interval" , 1);   //  上传进度通知间隔时长（单位：s）
        swfobj.addParam('allowFullscreen','true');
        swfobj.addParam('allowScriptAccess','always');
        swfobj.addParam('wmode','transparent');
        swfobj.write('swfDiv');
        // 加载上传flash ------------- end        
    <?php $this->endBlock() ?>
</script>