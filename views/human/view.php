<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\HumanForm */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="human-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    if ($model['is_active'] == 1) {
        ?>
        <p>
            <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>
        <?php
    }
    ?>
    <h3>QR код:</h3>
    <?= Html::img(['human/getqrcode', 'id' => $model->id]); ?>
    <p></p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'id_role',
            'human_name',
            'email:email',
            'telephone',
            'created_at',
            'modified_at',
            'is_active',
            'modified_by',
        ],
    ]) ?>

</div>
