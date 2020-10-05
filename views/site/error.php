<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        Возникла ошибка при обработке вашего запроса. Если вы уверены, что это сбой в работе системы, пожалуйста, напишите нам <a href="mailto:it@nsteam.ru">it@nsteam.ru</a>, изложив суть проблемы и приложив скриншот.
    </p>


</div>
