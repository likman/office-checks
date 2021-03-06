<?php

use app\components\PermissionManager;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\checks\EventHumanSearch */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $event app\models\checks\Event */

$this->title = 'Участвующие в мероприятии';
$this->params['breadcrumbs'][] = ['label' => 'Мероприятия', 'url' => ['event/index']];
$this->params['breadcrumbs'][] = $this->title;
$style = <<< CSS

   .grid-view td{ white-space: pre-line; }
   .grid-view th{ white-space: pre-line; }

CSS;
$this->registerCss($style); //чтобы слова в колонка переносились
?>
<div class="event-index">

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

    <p>
        <?php
        if (PermissionManager::can("EventHuman update")) {
            echo Html::a('Добавить', ['create', 'id_event' => $searchModel->id_event], ['class' => 'btn btn-success']);
        }
        ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $searchModel->getColumns(),
        'containerOptions' => ['style' => 'overflow: auto'],
        'toolbar' => [
            '{export}',
            '{toggleData}'
        ],
        'tableOptions' => ['class' => 'table-adaptive'],
        /*  'rowOptions'=>function ($model) {
              if($model['id_event_bind_status']!=1)
              {
                  return ['class' => 'danger'];
              }
          },*/
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
