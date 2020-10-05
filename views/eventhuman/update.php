<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\stewards\EventStewards */

$this->title = 'Редактирование: ' . $model->id_human;
$this->params['breadcrumbs'][] = ['label' => 'Мероприятия', 'url' => ['event/index']];
$this->params['breadcrumbs'][] = ['label' => 'Участвующие мероприятия', 'url' => ['eventhuman/index', 'id_event' => $model->id_event]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="eventhuman-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
