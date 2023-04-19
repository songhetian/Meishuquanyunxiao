<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\FollowCc */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Follow Ccs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="follow-cc-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'user_id',
            'user_type',
            'cc_id',
            'status',
            'timer:datetime',
        ],
    ]) ?>

</div>
