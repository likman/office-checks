<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\checks\EventType */

$this->title = 'Добавить тип мероприятия';
$this->params['breadcrumbs'][] = ['label' => 'Типы мероприятий', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="place-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
