<?php

use app\components\Helper;
use app\models\Role;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\stewards\Human */
/* @var $form yii\widgets\ActiveForm */
/* @var $success_message string */
/* @var $error_message string */
?>

<div class="human-form">

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'enableAjaxValidation' => true,
    ]); ?>

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

    <?= $form->field($model, 'id_role')->widget(Select2::class,
        ["data" => ArrayHelper::map(Role::getList(), 'id', 'name'),
            "options" =>
                ['placeholder' => 'Выберите...', 'id' => 'id_role'],
            'language' => 'ru',
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '100%'
            ],
        ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'telephone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, "work_time_start")->widget(DateTimePicker::class, [
        'options' => ['placeholder' => 'Время начала рабочего дня ...'],
        'convertFormat' => true,
        'pluginOptions' => [
            'startView' => 1,
            'format' => 'H:i',
            'todayHighlight' => true
        ]
    ]); ?>

    <?= $form->field($model, "work_time_end")->widget(DateTimePicker::class, [
        'options' => ['placeholder' => 'Время конца рабочего дня ...'],
        'convertFormat' => true,
        'pluginOptions' => [
            'startView' => 1,
            'format' => 'H:i',
            'todayHighlight' => true
        ]
    ]); ?>

    <?= $form->field($model, 'password')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
