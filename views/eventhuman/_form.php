<?php

use app\models\Human;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\checks\EventHuman */
/* @var $form yii\widgets\ActiveForm */
/* @var $event app\models\checks\Event */
?>


<div class="human-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
    ]); ?>

    <?= $form->field($model, 'id_human')->widget(Select2::class,
        ["data" => ArrayHelper::map(Human::getList(), 'id', 'name'),
            "options" =>
                ['placeholder' => 'Выберите...', 'id' => 'id'],
            'language' => 'ru',
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '100%'
            ],
        ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
