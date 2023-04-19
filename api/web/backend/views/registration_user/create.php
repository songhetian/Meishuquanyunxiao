<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\RegistrationUser */

$this->title = 'Create Registration User';
$this->params['breadcrumbs'][] = ['label' => 'Registration Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
