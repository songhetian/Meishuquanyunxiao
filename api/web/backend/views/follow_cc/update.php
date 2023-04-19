<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\FollowCc */

$this->title = 'Update Follow Cc: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Follow Ccs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="follow-cc-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
