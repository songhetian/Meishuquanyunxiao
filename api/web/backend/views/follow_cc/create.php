<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\FollowCc */

$this->title = 'Create Follow Cc';
$this->params['breadcrumbs'][] = ['label' => 'Follow Ccs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="follow-cc-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
