<?php

use app\models\checks\EventType;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\checks\Event */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="event-form">

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'enableAjaxValidation' => true,
    ]); ?>

    <?= $form->field($model, 'id_event_type')->widget(Select2::class,
        ["data" => ArrayHelper::map(EventType::getList(), "id", "name"),
            "options" =>
                ['placeholder' => 'Выберите...', 'id' => 'id_event_type'],
            'language' => 'ru',
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '100%'
            ],
        ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => '6']) ?>

    <?= $form->field($model, "start_time")->widget(DateTimePicker::class, [
        'language' => 'ru',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd hh:ii:ss'
        ]
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
