<?php

use app\components\PermissionManager;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\checks\Event */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Мероприятия', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    if ($model['deleted']==0) {
        ?>
        <p>
            <?php
            if (PermissionManager::can("Event update")) {
                echo Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']);
                echo Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Вы уверены, что хотите удалить?',
                        'method' => 'post',
                    ],
                ]);
            }
            ?>
        </p>
        <?php
    }
?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description',
            'id_event_type',
            'start_time',
            'created_at',
            'modified_at',
            'is_active',
            'modified_by',
        ],
    ]) ?>

</div>
