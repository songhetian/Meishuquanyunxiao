<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\RegistrationUser */

$this->title = $model->studio_id;
$this->params['breadcrumbs'][] = ['label' => 'Registration Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->studio_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->studio_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'studio_id',
            'user_id',
            'user_type',
            'timer:datetime',
            'status',
        ],
    ]) ?>

</div>
