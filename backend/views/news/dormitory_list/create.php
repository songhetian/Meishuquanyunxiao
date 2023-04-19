<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\DormitoryList */

$this->title = 'Create Dormitory List';
$this->params['breadcrumbs'][] = ['label' => 'Dormitory Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dormitory-list-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
