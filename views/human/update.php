<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\HumanForm */

$this->title = 'Редактирование: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="human-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
