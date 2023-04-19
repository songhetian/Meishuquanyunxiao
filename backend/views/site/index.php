<?php 

use yii\helpers\Html; 
use common\models\Format;

?>
<div id="content" class="col-md-12"> 
 <div class="pageheader"> 
  <h2><i class="fa fa-tachometer"></i> <?= Yii::t('backend', 'Index') ?> </h2> 
  <div class="breadcrumbs"> 
   <ol class="breadcrumb"> 
    <li>当前位置</li> 
    <li class="active"><?= Yii::t('backend', 'Index') ?></li> 
   </ol> 
  </div> 
 </div> 
 <div class="main"> 
  <div class="row cards"> 
   <div class="card-container col-lg-3 col-sm-6 col-sm-12"> 
    <div class="card card-redbrown hover"> 
     <div class="front"> 
      <div class="media"> 
       <span class="pull-left"> <i class="fa fa-users media-object"></i> </span> 
       <div class="media-body"> 
        <small>学生数量 (总数:<?= Format::getStudio('review_num') ?>)</small> 
        <h2 class="media-heading animate-number" data-value="<?= $count['user']; ?>" data-animation-duration="<?= Format::getStudio('review_num') ?>"><?= $count['user']; ?></h2> 
       </div> 
      </div>
     </div> 
    </div> 
   </div>
  </div> 
 </div> 
</div> 