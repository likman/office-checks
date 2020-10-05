<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\checks\EventCheckSearch */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $event app\models\checks\Event */

$this->title = 'Отметки';
$this->params['breadcrumbs'][] = ['label' => 'Мероприятия', 'url' => ['event/index',]];
$this->params['breadcrumbs'][] = $this->title;
$style = <<< CSS

   .grid-view td{ white-space: pre-line; }
   .grid-view th{ white-space: pre-line; }

CSS;
$this->registerCss($style);
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $event,
        'attributes' => [
            'id',
            'name',
            'start_time',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $searchModel->getColumns(),
        'containerOptions' => ['style' => 'overflow: auto'], // only set whe4n $responsive = false
        'toolbar' => [
            '{export}',
            '{toggleData}'
        ],
        'tableOptions' => ['class'=>'table-adaptive'],
        'rowOptions'=>function ($model) {
            if (!isset($model['check_time_in'])) {
                return ['class' => 'danger'];
            }
        },
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
        'pjax' => true,
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,
        'resizableColumns' => false,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY
        ],
    ]); ?>
</div>
