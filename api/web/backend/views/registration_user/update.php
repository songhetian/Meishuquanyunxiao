<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\RegistrationUser */

$this->title = 'Update Registration User: ' . $model->studio_id;
$this->params['breadcrumbs'][] = ['label' => 'Registration Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->studio_id, 'url' => ['view', 'id' => $model->studio_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="registration-user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
