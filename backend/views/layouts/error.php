<?php 

use yii\helpers\Html; 

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
  
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="<?= Yii::$app->charset ?>" />
    <?= Html::csrfMetaTags() ?>
    <title>哎呀…您访问的页面报错了</title>
    <link href="<?= Yii::$app->request->baseUrl; ?>/assets/css/error.css" rel="stylesheet">
    <?php $this->head() ?>
  </head>

  <body>
    <?php $this->beginBody() ?>
    <?= $content ?>
   	<?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>