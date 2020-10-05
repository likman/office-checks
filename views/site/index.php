<?php


/* @var $this yii\web\View */
/* @var $events_for_check Event */

/* @var $id_human integer */

use app\components\PermissionManager;
use app\models\checks\Event;
use yii\helpers\Html;

$this->title = Yii::$app->name;
?>
<div class="site-index">

    <div class="body-content rounded-list">
        <div class="row">
            <div class="col-lg-4">
                <h3>Ваш QR код:</h3>
                <?= Html::img(['human/getmyqrcode']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <h2>Управление данными</h2>

                <ul>
                    <?php
                        if (PermissionManager::can("Human")) {
                            ?>
                            <li>
                                <?= Html::a('Сотрудники', ['human/index']); ?>
                            </li>
                            <?php
                        }
                    ?>
                    <?php
                    if (PermissionManager::can("Event")) {
                        ?>
                        <li>
                            <?= Html::a('Мероприятия', ['event/index']); ?>
                        </li>
                        <?php
                    }
                    ?>
                    <?php
                    if (PermissionManager::can("EventType")) {
                        ?>
                        <li>
                            <?= Html::a('Типы мероприятий', ['eventtype/index']); ?>
                        </li>
                        <?php
                    }
                    ?>
                    <?php
                    if (PermissionManager::can("Import")) {
                        ?>
                        <li>
                            <?= Html::a('Импорт данных', ['import/index']); ?>
                        </li>
                        <?php
                    }
                    ?>
                    <?php
                    if (PermissionManager::can("RdpAuth")) {
                        ?>
                        <li>
                            <?= Html::a('Получить доступ к рабочему столу', ['site/rdpauth']); ?>
                        </li>
                        <?php
                    }
                    ?>
                    <?php
                    if (PermissionManager::can("EventCheck self check") && isset($events_for_check)) {
                        foreach ($events_for_check as $id_event => $event_name) {
                            ?>
                            <li>
                                <?= Html::a('Отметить прибытие за ' . $event_name, ['eventcheck/checkin', 'id_event' => $id_event, 'id_human' => $id_human]); ?>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
