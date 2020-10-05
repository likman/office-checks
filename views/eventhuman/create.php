<?php

use yii\helpers\Html;
use yii\widgets\DetailView;


/* @var $this yii\web\View */
/* @var $model app\models\checks\EventHuman */
/* @var $event app\models\checks\Event */

$this->title = 'Участие в мероприятии';
$this->params['breadcrumbs'][] = ['label' => 'Мероприятия', 'url' => ['event/index']];
$this->params['breadcrumbs'][] = ['label' => 'Участвующие мероприятия', 'url' => ['eventhuman/index', 'id_event' => $model->id_event]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="eventhuman-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $event,
        'attributes' => [
            'id',
            'name',
            'description',
            'start_time',
        ],
    ]) ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
