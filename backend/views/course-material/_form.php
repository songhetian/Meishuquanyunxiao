<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\assets\PagingAsset;
use backend\models\Admin;
use common\models\Campus;
use common\models\Group;
use common\models\Format;
use components\Oss;

PagingAsset::register($this);

$configs = [
  [
    'tag' => 'picture',
    'name' => Yii::t('backend', 'Picture'),
    'type' => Group::TYPE_PICTURE,
    'table' => \common\models\Picture::className(),
    'field' => 'image'
  ],
  [
    'tag' => 'video',
    'name' => Yii::t('backend', 'Video'),
    'type' => Group::TYPE_VIDEO,
    'table' => \common\models\Video::className(),
    'field' => 'preview'
  ],
];

/* @var $this yii\web\View */
/* @var $model common\models\CourseMaterial */
/* @var $form yii\widgets\ActiveForm */
?>
<style type="text/css">
  .as-table{background:#FFF; margin: 0; padding:10px;}
  #picture,#video{display: none;}
  .mleft{float: left; width: 20%;}
  .mright{float: left; width: 80%;}
  .mleft .selected{background : #f5f5f5;}
  .modal .fade{display: none;}
  .open {float: right;}
  .img-view {width: 15%; display: inline-block; padding: 0; margin: 1% 0% 1% 1.2%;}
  .tile {display: block !important;}
  .tile-navbar h5 {padding: 0 15px;}
  .filters{float:none !important; border:0 !important; margin-bottom: 20px;}
  .filters-video{margin: 20px 0px 0px 0px;height: 100%;}
  .panel-group{padding: 0px 10px 0px 10px;}
  .mright .open{margin-top: -50px;}
  .cover-title{position:absolute;margin-left:1%;margin-top:0.5%;}
  .cover-count{position:absolute;margin-left:1%;margin-top:7.5%;}
  ::-webkit-input-placeholder { color: black !important;}
  :-moz-placeholder { color: black !important;}
  ::-moz-placeholder { color: black !important;}
  :-ms-input-placeholder { color: black !important;}
  .search-result {
    padding: 5px 15px;
    margin: 0;
    color: rgba(255, 255, 255, 0.5);
    font-size: 18px;
    line-height: 30px;
  }
  .controls {float: right}
</style>
<ul class="nav nav-tabs" style="width:190px;">
    <li class="active">
      <a href="#" correlated="#image-text" data-toggle="tab">
        <?= Yii::t('backend', 'Courseware') ?>
      </a>
    </li>
    <? foreach ($configs as $config) : ?>
      <li>
        <a href="#" correlated="#<?= $config['tag'] ?>" data-toggle="tab">
          <?= $config['name'] ?>
        </a>
      </li>
    <? endforeach; ?> 
</ul>
<div class="as-table">
  <!-- 课件 -->
  <div id="image-text">
      <div class="course-material-form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="form-group" style="float:right;margin-top:-50px;z-index:-1;">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['onclick' => 'submitVal()', 'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        <div style="clear:both;"></div>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= \crazydouble\ueditor\UEditor::widget([
            'model' => $model,
            'attribute' => 'description',
            'config' => [
               'toolbars' => Yii::$app->params['ueditor']['toolbars']
            ]
        ]) ?>
        
        <div class="hide">
            <?= $form->field($model, 'picture')->hiddenInput() ?>
            <?= $form->field($model, 'video')->hiddenInput() ?>
        </div>

        <?php
        /*
        <?= $form->field($model, 'admin_id')->textInput() ?>

        <?= $form->field($model, 'is_public')->textInput() ?>

        <?= $form->field($model, 'created_at')->textInput() ?>

        <?= $form->field($model, 'updated_at')->textInput() ?>

        <?= $form->field($model, 'status')->textInput() ?>
        */
        ?>

        <?php ActiveForm::end(); ?>       
      </div>
  </div>
  <? foreach ($configs as $config) : ?>
    <!-- 内容 -->
    <div id="<?= $config['tag'] ?>">
        <? if(!empty($model->id)) : ?>
            <? $groups = Group::getGroupList($model->id, $config['type']); ?>
        <? endif ?>
        <div class="mleft">
            <h4>
              <strong><?= $config['name'] ?> - 分组列表</strong>
              <a href="#<?= $config['tag'] ?>-group" class="pull-right" data-toggle="modal"><i class="fa fa-plus"></i></a>
            </h4>
            <span class='create-num' num='2'></span>
            <table class="table" >
                <tbody>
                    <? if($groups) : ?>
                        <? $num = 1; ?>
                        <? foreach ($groups as $group) : ?>
                            <tr correlated="#<?= $config['tag'] ?>-<?= $group->id ?>" <? if($num == 1){ echo "class='selected'"; } ?>>
                                <td title="<?= $group->name ?>">
                                  <?= $group->name ?>
                                  <div class="controls">
                                    <a class="group_delete" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                  </div>
                                </td>
                            </tr>
                            <? $num++; ?>
                        <? endforeach; ?> 
                    <? else : ?>
                        <tr correlated="#<?= $config['tag'] ?>-1" class='selected'>
                            <td title="必看<?= $config['name'] ?>">
                              必看<?= $config['name'] ?>
                              <div class="controls">
                                <a class="group_delete" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                              </div>
                            </td>
                        </tr>
                    <? endif ?> 
                </tbody>
            </table>
        </div>
        <div class="mright">
            <div class='open'>
              <?= Html::a(
                '本地' . $config['name'],
                '#' . $config['tag'] . '-material',
                [
                  'class' => 'btn btn-red',
                  'source' => $config['table']::SOURCE_LOCAL,
                  'table' => $config['table'],
                  'type' => $config['type'],
                  'tag' => $config['tag'],
                  'field' => $config['field'],
                  'correlated' => '#' . $config['tag'] . '-material',
                  'data-toggle' => 'modal'
                ]
              ) ?>
              <?= Html::a(
                '美术圈' . $config['name'],
                '#' . $config['tag'] . '-metis',
                [
                  'class' => 'btn btn-primary',
                  'source' => $config['table']::SOURCE_METIS,
                  'type' => $config['type'],
                  'table' => $config['table'],
                  'tag' => $config['tag'],
                  'field' => $config['field'],
                  'correlated' => '#' . $config['tag'] . '-metis',
                  'data-toggle' => 'modal'
                ]
              ) ?>
            </div>
            <div class="material-list">
                <? if($groups) : ?>
                    <? $num = 1; ?>
                    <? foreach ($groups as $group) : ?>
                        <div id="<?= $config['tag'] ?>-<?= $group->id ?>" <? if($num != 1){ echo "style='display:none;'"; }?>>
                            <?
                              $table = $config['table'];
                              $ids = Format::explodeValue($group->material_library_id); 
                              $materials = $table::findAll($ids); 
                            ?>
                            <? foreach ($materials as $material) : ?>
                                <div class="img-view">
                                  <?
                                    $size = Yii::$app->params['oss']['Size']['250x250'];
                                    if($material->source == $table::SOURCE_LOCAL){
                                      $studio = Campus::findOne(Admin::findOne($material->admin_id)->campus_id)->studio_id;
                                      $src = Oss::getUrl($studio, $config['tag'], $config['field'], $material->{$config['field']});
                                    }else{
                                      $src = $material->{$config['field']};
                                    }
                                  ?>
                                  <img alt="<?= $material->id ?>" src="<?= $src . $size ?>">
                                  <div class="img-button">
                                    <? if($config['type'] == Group::TYPE_PICTURE) : ?>
                                      <button type="button" src="<?= $src . $size ?>" class="btn btn-primary btn-xs margin-bottom-20 larger">查看</button>
                                      <a href="<?= $src . $size ?>" download="<?= time() ?>" class="btn btn-primary btn-xs margin-bottom-20 down">下载</a>
                                    <? else : ?>
                                      <a href="#video-play" cc_id="<?= $material->cc_id ?>" data-toggle="modal" class="btn btn-primary btn-xs margin-bottom-20 play">播放</a>
                                    <? endif ?>
                                    <button type="button" class="btn btn-primary btn-xs margin-bottom-20 delete">删除</button>
                                  </div>
                                </div>
                                
                            <? endforeach; ?> 
                        </div>
                    <? $num++; ?>
                    <? endforeach; ?>
                <? else: ?>
                    <div id="<?= $config['tag'] ?>-1"></div>
                <? endif ?> 
            </div>
        </div>
    </div>
    <!-- 分组 -->
    <div class="modal fade" id="<?= $config['tag'] ?>-group">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">关闭</button>
              <h3 class="modal-title thin">添加 <?= $config['name'] ?>分组</h3>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label class="control-label">分组名称 *</label>
                <input type="text" class="form-control" correlated="#<?= $config['tag'] ?>">
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-red" data-dismiss="modal">取消</button>
              <button class="add-group btn btn-green" data-dismiss="modal">添加</button>
            </div>
          </div>
        </div>
    </div>
    <!-- 本地资源 -->
    <div class="modal fade" id="<?= $config['tag'] ?>-material">
      <div class="modal-dialog" style="width:90%;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">关闭</button>
            <h3 class="modal-title thin">本地<?= $config['name'] ?></h3>
          </div>
          <div class="modal-body"></div>
          <div class="modal-page">
            <div class="<?= $config['tag']?>LocalPageToolbar"></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-red" data-dismiss="modal">取消</button>
            <button class="add-material btn btn-green" source="local" correlated="#<?= $config['tag'] ?>" data-dismiss="modal">添加</button>
          </div>
        </div>
      </div>
    </div>
    <!-- 美术圈资源 -->
    <div class="modal fade" id="<?= $config['tag'] ?>-metis">
      <div class="modal-dialog" style="width:90%;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">关闭</button>
            <h3 class="modal-title thin">美术圈<?= $config['name'] ?></h3>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-page">
            <div class="<?= $config['tag']?>PageToolbar"></div>
            <div class="<?= $config['tag']?>SearchPageToolbar" style="display:none;"></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-red" data-dismiss="modal">取消</button>
            <button class="add-material btn btn-green" source="metis-<?= $config['tag'] ?>" correlated="#<?= $config['tag'] ?>" data-dismiss="modal">添加</button>
          </div>
        </div>
      </div>
    </div>
  <? endforeach; ?> 
  <div class="modal fade" id="video-play">
        <div class="modal-dialog" style="width:50%;">
          <div class="modal-content">
            <div class="modal-body" style="background:#000;">
              <div class='spark' style='margin:0 auto;'></div>
            </div>
            <div class="modal-footer" style="margin-top:0px;">
            <button class="btn btn-red pull-center" data-dismiss="modal">关闭</button>
          </div>
          </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function getParams(thisDom){
      var tile = thisDom.parents('.tile');
      var navbar = thisDom.parents('.tile-navbar');
      var type = navbar.attr('type');
      var vid = thisDom.attr('vid');
      var line = thisDom.parents('.filters').attr('line');
      var url = thisDom.parents('.filters').attr('url');
      var where = {type : type};
      if(line == 1){
        where.category_id = vid;
      }else if(line == 2){
        where.category_id = navbar.find(".category > .active").attr('vid');
        where.category_child_id = vid;
      }else{
        var keyword_id = '';
        navbar.find('.keyword > .active').each(function(){
            keyword_id += $(this).attr('vid') + '-';
        });
        where.category_id = navbar.find(".category > .active").attr('vid');
        where.category_child_id = navbar.find(".category-child > .active").attr('vid');
        where.keyword_id = (keyword_id) ? keyword_id.substring(0, keyword_id.length - 1) : 0;
        
      }
      var publishing_id = navbar.find('.publishing > .active').attr('vid');
      where.publishing_id = (publishing_id) ? publishing_id : 0;

      $.get(url, where, function(data){
        dataProcessing(data, line, tile, navbar, type, where.keyword_id, where.publishing_id);
      });
    }

    function dataProcessing(data, line, tile, navbar, type, keyword_id = 0, publishing_id = 0, page = 0){
      var hidden = false;
      var div = '';
      var tag = (type == 10) ? 'picture' : 'video';
      var filters = '.filters-' + tag;
      //替换分类和关键字
      if(line == 1 || line == 2){
        if(line == 1){
          navbar.find('.category-child').html(data.category_child);
        }
        navbar.find('.keyword').html(data.keyword);
      }else if((line == 3 && !keyword_id) || (line == 4 && !publishing_id)){
        hidden = true;
      }

      div += 'category' + navbar.find('.category > .active').attr('vid') 
      div += '-category-child' + navbar.find('.category-child > .active').attr('vid');  
      if(keyword_id){ div += '-keyword' + keyword_id; }
      if(publishing_id){ div += '-publishing_id' + publishing_id; }
      div += '-page' + page;
      if(line != 0){
        size = (type == 10) ? 30 : 5;
        toolbar = $('.' + tag + 'PageToolbar');
        toolbar.html('');
        toolbar.Paging({
          pagesize:size,
          count:data.count,
          current:page + 1,
          toolbar:true,
          correlated:'#' + tag + '-metis'
        });
      }
      
      if(hidden == true){
        tile.find(filters + ' > .' + div).show().siblings().hide();
        return true;
      }
      //判断DIV是否存在
      var isset = $(filters + ' > div').hasClass(div);
      if(isset){
        tile.find(filters + ' > .' + div).show().siblings().hide();
      }else{
        tile.find(filters + ' > div').hide();
        tile.find(filters).append('<div class="' + div + '">' + data.material + '</div>');
      }
    }

    function searchDataProcessing(data, type, search, page = 0){
      var tag = (type == 10) ? 'picture' : 'video';
      var size = (type == 10) ? 30 : 5;
      var filter_view = $('.' + tag + '-filter-view');
      var search_view = $('.' + tag + '-search-view');
      var filter_toolbar = $('.' + tag + 'PageToolbar');
      var search_toolbar = $('.' + tag + 'SearchPageToolbar');
      
      $(search_view).find('.' + tag + 'search_num').html(data.count);
      //判断DIV是否存在
      div = tag + '-search-' + search + '-page' + page;
      var search_tag = '.search-' + tag;
      var isset = $(search_tag + '> div').hasClass(div);
      if(isset){
        search_view.find(search_tag + ' > .' + div).show().siblings().hide();
      }else{
        search_view.find(search_tag + ' > div').hide();
        search_view.find(search_tag).append('<div class="' + div + '">' + data.material + '</div>');
      }
      search_view.show();

      search_toolbar.html('');
      search_toolbar.Paging({
        pagesize:size,
        count:data.count,
        current:page + 1,
        toolbar:true,
        correlated:'#' + tag + '-metis',
        search:search
      });
      search_toolbar.show();

      filter_view.hide();
      filter_toolbar.hide();
    }

    function submitVal(){
      appendHiddenVal('picture');
      appendHiddenVal('video');
    }

    function appendHiddenVal(tag){
      var trs = $('#' + tag + ' .table tr');
      var input = $('#coursematerial-' + tag);
      var val = '';
      trs.each(function(){
        var correlated = $(this).attr('correlated');
        var name = $(this).find('td').attr('title');
        var imgs = $(correlated).find('.img-view > img');
        var img = '';
        if(imgs.length > 0){
          for (var i = 0; i < imgs.length; i++) {
            img += imgs[i].alt + ',';
          }
          val += name + '-' + img.substring(0, img.length - 1) + '/';
        }else{
          val += name + '-' + '/';
        }
      });
      input.val(val);
    }

    function connectHtml(correlated, selected, alt, src, cc_id){
      var time = "<?= time() ?>";
      var html = "<div class='img-view'>";
      html += "<img alt='" + alt + "' src='" + src + "'>";
      html += "<div class='img-button'>";
      if(correlated == '#picture'){
        html += "<button type='button' src='" + src + "' class='btn btn-primary btn-xs margin-bottom-20 larger'>查看</button> ";
        html += "<a href='" + src + "' download='" + time + "' class='btn btn-primary btn-xs margin-bottom-20 down'>下载</a> ";
      }else{
        html += "<a href='#video-play' cc_id=" + cc_id + " data-toggle='modal' class='btn btn-primary btn-xs margin-bottom-20 play'>播放</a> ";
      }
      html += "<button type='button' class='btn btn-primary btn-xs margin-bottom-20 delete'>删除</button>";
      html += "</div>";
      html += "</div>";
      $(selected).append(html);
    }

    <?php $this->beginBlock('js_end') ?>
      //同步Ajax
      $.ajaxSetup({
          async : false 
      });

      //切换类型
      $('.nav > li > a').click(function(){
        var correlated = $(this).attr('correlated');
        $(correlated).show().siblings().hide();
      });
      
      //获取素材库数据
      $('.open > a').click(function(){
        var source = $(this).attr('source');
        var type = $(this).attr('type');
        var table = $(this).attr('table');
        var tag = $(this).attr('tag');
        var field = $(this).attr('field');
        var correlated = $(this).attr('correlated');
        var url = "<?= Yii::$app->urlManager->createUrl(['course-material/get-materials']) ?>";
        var page = 0;
        var where = {page : page, source : source, type : type, table: table, tag : tag, field : field};
        $.get(url, where, function(data){
          $(correlated).find('.modal-body').html(data.material);
          size = (type == 10) ? 30 : 5;
          if(source == 10){
            local_toolbar = '.' + tag + 'LocalPageToolbar';
            $(local_toolbar).html('');
            $(local_toolbar).Paging({
              pagesize:size,
              count:data.count,
              toolbar:true,
              correlated:'#' + tag + '-material',
              table:table,
              tag:tag,
              field:field
              });
          }else{
            filter_toolbar = '.' + tag + 'PageToolbar';
            search_toolbar = '.' + tag + 'SearchPageToolbar';
            $(filter_toolbar).html('');
            $(filter_toolbar).Paging({pagesize:size,count:data.count,toolbar:true,correlated:'#' + tag + '-metis'});
            $(filter_toolbar).show();
            $(search_toolbar).hide();
          }
       });
      });

      //单选
      $(document).on('click', '.select > a', function () {
        $(this).addClass('active').siblings().removeClass('active');
        getParams($(this));
      });

      //多选
      $(document).on('click', '.multi-select > a', function () {
        $(this).toggleClass('active');
        getParams($(this));
      });

      //搜索
      $(document).on('click', '.input-group-btn', function () {
        var search = $(this).prev().val();
        if(search){
          var url = "<?= Yii::$app->urlManager->createUrl(['course-material/metis-search']) ?>";
          var type = $(this).parents('.search-bar').attr('type');
          var where = {type : type, search : search};
          $.get(url, where, function(data){
            searchDataProcessing(data, type, search);
          });
        }
      });

      //点击返回
      $(document).on('click', '.back', function () {
        var type = $(this).attr('tag');
        var tag = (type == 10) ? 'picture' : 'video';
        var filter_view = $('.' + tag + '-filter-view');
        var search_view = $('.' + tag + '-search-view');
        var filter_toolbar = $('.' + tag + 'PageToolbar');
        var search_toolbar = $('.' + tag + 'SearchPageToolbar');
        search_view.hide();
        search_toolbar.hide();
        filter_view.show();
        filter_toolbar.show();
      });

      //分页
      $(document).on('click', '.ui-pager', function () {
        var ul = $(this).parents('.paging');
        var focus = Number(ul.find('.focus').attr('data-page'));
        //获取页码
        if ($(this).hasClass('js-page-first')) {
          page = 1;
        }else if ($(this).hasClass('js-page-prev')) {
          page = focus - 1;
        }else if ($(this).hasClass('js-page-next')) {
          page = focus + 1;
        }else if ($(this).hasClass('js-page-last')) {
          page = $(ul).attr('count');
        }else if ($(this).hasClass('jump')) {
          page = Number(ul.find('.ui-paging-count').val());
          max = Number(ul.find('.js-page-next').prev().attr('data-page'));
          if(page > max){
            page = max;
          }
        }else{
          page = $(this).attr('data-page');
        }
        page = page - 1;

        var correlated = ul.attr('correlated');
        var arr = correlated.split('-');
        if(arr[1] == 'material'){
          var table = ul.attr('table');
          var tag = ul.attr('tag');
          var field = ul.attr('field');
          var where = {page : page, table : table, tag : tag, field : field};
          var url = "<?= Yii::$app->urlManager->createUrl(['course-material/get-page']) ?>";
          $.get(url, where, function(data){
            main_div = tag + '-page';
            div = main_div + '-' + page;
            var isset = $('div').hasClass(div);
            if(isset){
              $('.' + div).show().siblings().hide();
            }else{
              $('.' + main_div + ' > div').hide();
              $('.' + main_div).append('<div class="' + div + '">' + data.material + '</div>');
            }
          });
        }else{
          var search = ul.attr('search');
          var navbar = $(correlated).find('.tile-navbar');
          var type = navbar.attr('type');
          if(search){
            var url = "<?= Yii::$app->urlManager->createUrl(['course-material/metis-search']) ?>";
            var where = {type : type, search : search, page : page};
            $.get(url, where, function(data){
              searchDataProcessing(data, type, search, page);
            });
          }else{
            var tile = $(correlated).find('.tile');
            var category_id = navbar.find('.category-child > .active').attr('vid');
            var keyword_id = '';
            navbar.find('.keyword > .active').each(function(){
              keyword_id += $(this).attr('vid') + '-';
            });
            if(keyword_id){
              var keyword_id = keyword_id.substring(0, keyword_id.length - 1);
            }
            var publishing_id = navbar.find('.publishing > .active').attr('vid');

            var where = {page : page, type : type, vid : category_id, keyword_id : keyword_id, publishing_id : publishing_id};
            var url = "<?= Yii::$app->urlManager->createUrl(['course-material/get-metis-page']) ?>";
            $.get(url, where, function(data){
              dataProcessing(data, 0, tile, navbar, type, keyword_id, publishing_id, page);
            });
          }
        }
      });

      //添加分组
      $('.add-group').click(function(){
        var input = $(this).parents('.modal.fade').find('input');
        var name = input.val();
        if(name){
          var txt = new RegExp(/[-,\/]/);
          if(txt.test(name)){
            alert('禁止使用特殊字符,请重新创建!');
            input.val('');
            return;
          }
          var correlated = input.attr('correlated');
          var tbody = $(correlated + ' tbody');
          var tds = tbody.find('td');
          //判断分组名称是否存在
          for (var i = 0; i < tds.length; i++) {
            if(tds[i].title == name){
              alert('该分组名称已存在,请重新创建!');
              input.val('');
              return;
            }
          }
          var number = $('.create-num').attr('num');
          tbody.append("<tr correlated='" + correlated + "-" + number + "'><td title='" + name + "'>" + name + "<div class='controls'><a class='group_delete' href='javascript:void(0);'><i class='fa fa-times'></i></a></div>" + "</td></tr>");
          $(correlated + ' .material-list').append("<div id='" + correlated.substring(1) + "-" + number + "' style='display:none;'></div>");
          input.val('');
          $('.create-num').attr('num', parseInt(number) + 1);
        }
      });

      //选中分组
      $(document).on('click', '.table tr', function () {
          var correlated = $(this).attr('correlated');
          $(this).addClass('selected').siblings().removeClass('selected');
          $(correlated).show().siblings().hide();
      });

      //删除分组
      $(document).on('click', '.controls .group_delete', function () {
          var table = $(this).parents('.table');
          var length = $(table).find('tr').length;
          if(length > 1){
            var status = confirm('分组删除后选择的素材也会被一并删除,确定?');
            if(status == true){
              var tr = $(this).parents('tr');
              var correlated = tr.attr('correlated');
              $(correlated).remove();
              $(tr).remove();
              $(table).find('tr:first').trigger('click');
            }
          }else{
            alert('删除失败,必须存在一个分组!');
          }
      });

      $(document).on("click", ":checkbox", function () {
          $(this).parents('.img-view').toggleClass("selected");
      });

      //添加素材
      $(".add-material").click(function(){
        var imgs = $(this).parents('.modal.fade').find('.img-view.selected > img');
        if(imgs.length > 0){
          var source = $(this).attr('source');
          var correlated = $(this).attr('correlated');
          var selected = $(correlated + " .table .selected").attr('correlated');
          imgs.each(function(){
            //美术圈素材处理
            alt = $(this).attr('alt');
            src = $(this).attr('src');
            cc_id = $(this).attr('cc_id');
            if(source == 'local'){
              connectHtml(correlated, selected, alt, src, cc_id);
            }else{
              if(correlated == '#picture'){
                url = "<?= Yii::$app->urlManager->createUrl(['course-material/save-image']) ?>";
              }else{
                url = "<?= Yii::$app->urlManager->createUrl(['course-material/save-preview']) ?>";
              }
              $.get(url, {id:alt}, function(data){
                connectHtml(correlated, selected, data.alt, data.src, data.cc_id);
              });
            }
          });
        }
      });
      
      //查看
      $(document).on('click','.larger',function(){
          var url = $(this).attr('src');
          url = url.replace('250x250','1000x1000');
          window.open(url);
      });
      
      //播放视频
      $(document).on("click", ".play", function () {
        var cc_id = $(this).attr('cc_id');
        var url = "<?= Yii::$app->urlManager->createUrl(['course-material/get-spark']) ?>";
        $.get(url, {cc_id : cc_id}, function(data){
          $('.spark').html(data);
        });
      });

      //下载
      $(document).on('click','.down',function(){
          var url = $(this).attr('href');
          $(this).attr('href', url.replace('250x250','1000x1000'));
      });

      //删除
      $(document).on('click','.delete',function(){
          var status = confirm('确认删除?');
          if(status == true){
            $(this).parents('.img-view').remove();
          }
      });
    <?php $this->endBlock() ?>
</script>