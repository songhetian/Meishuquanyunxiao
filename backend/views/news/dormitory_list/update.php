<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DormitoryList */

$this->title = 'Update Dormitory List: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Dormitory Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->dormitory_list_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="dormitory-list-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
