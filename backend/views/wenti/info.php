<?php 

use backend\assets\AppAsset; 
use yii\helpers\Html; 
use backend\models\Menu;
use common\models\Format;
AppAsset::register($this); 

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>常见问题</title>
    <?php $this->head() ?>
  </head>
  
  <body>
    <?php $this->beginBody() ?>
        <?= $data['info'];?>
    <?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>