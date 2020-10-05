<?php

use app\components\PermissionManager;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\stewards\HumanSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Сотрудники';
$this->params['breadcrumbs'][] = $this->title;
$style= <<< CSS

   .grid-view td{ white-space: pre-line; }
   .grid-view th{ white-space: pre-line; }

CSS;
$this->registerCss($style); //чтобы слова в колонка переносились
?>
<div class="human-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        if (PermissionManager::can("Human update")) {
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
            if ($model['is_active'] == 0) {
                return ['class' => 'danger'];
            }
        },
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
        'pjax' => true,
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => false,
        'responsiveWrap' => false,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,
        'resizableColumns' => false,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY
        ],
    ]); ?>
</div>
