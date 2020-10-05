<?php

use app\components\PermissionManager;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\checks\EventSearch */
/* @var $dataProvider yii\data\SqlDataProvider */

$this->title = 'Мероприятия';
$this->params['breadcrumbs'][] = $this->title;
$style= <<< CSS

   .grid-view td{ white-space: pre-line; }
   .grid-view th{ white-space: pre-line; }

CSS;
$this->registerCss($style); //чтобы слова в колонка переносились
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        if (PermissionManager::can("Event update")) {
            echo Html::a('Добавить', ['create'], ['class' => 'btn btn-success']);
        }
        ?>
    </p>
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
            if($model['deleted']==1)
            {
                return ['class' => 'danger'];
            } else if ($model['id_event_bind_status']=="1")
            {
                return ['class' => 'success'];
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
