<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */
/* @var $success_message string */

/* @var $error_message string */

use app\components\Helper;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Открыть доступ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    if (Helper::isOk($success_message)) {
        ?>
        <div class="alert alert-success fade in">

            <?= $success_message ?>

        </div>
        <?php
    }
    ?>
    <?php
    if (Helper::isOk($error_message)) {
        ?>
        <div class="alert alert-danger fade in">

            <?= $error_message ?>

        </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>
            <p>Введите код:</p>
            <?= Html::input('text','secret') ?>
            <p></p>
            <div class="form-group">
                <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
