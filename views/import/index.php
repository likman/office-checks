<?php

use app\components\Helper;
use app\models\ImportForm;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $success_message string */
/* @var $error_message string */

$this->title = 'Импорт данных';
$this->params['breadcrumbs'][] = $this->title;
if (YII_DEBUG == true) {
    $this->registerJsFile(Yii::$app->request->baseUrl . "/js/helper.js", ['depends' => ['\yii\web\JqueryAsset'], 'position' => yii\web\View::POS_HEAD,]);
} else {
    $this->registerJsFile(Yii::$app->request->baseUrl . "/js/helper.min.js", ['depends' => ['\yii\web\JqueryAsset'], 'position' => yii\web\View::POS_HEAD,]);
}
?>

<div class="list-reports-index">

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
    <div class="btn-group">
        <a class='btn btn-primary' onclick="showElement('report-fields-info'); return false;" href="#"
           rel="nofollow">Показать инструкции</a>
    </div>
    <p></p>
    <div id="report-fields-info" style="display:none;">
        <div class="import-report-info-block">
            <h3>Импорт сотрудников</h3>
            Отчет должен быть в формате CSV. На первой строке должны быть заголовки</br>
            <b>Код роли</b> (обяз.); 1 - Администратор, 2 - Сотрудник, 3 - Удаленщик, 4 - Робот.</br>
            <b>ФИО</b> (обяз.);</br>
            <b>Почта</b> (обяз.);</br>
            <b>Телефон</b> (обяз.) только цифры, например 89181234567;</br>
            <b>Пароль</b> (обяз.);</br>
            <?php
            echo Html::a("Скачать шаблон",Url::to(["files/template_import_human.csv"]),[ 'target'=>'_blank']).'</br>';
            ?>
        </div>
        <div class="import-report-info-block">
            <h3>Импорт записавшихся на мероприятие</h3>
            Отчет должен быть в формате CSV. На первой строке должны быть заголовки</br>
            <b>Код мероприятия</b> (обяз.);</br>
            <b>Код сотрудника</b> (обяз.);</br>
            <?php
            echo Html::a("Скачать шаблон",Url::to(["files/template_import_event_bindings.csv"]),[ 'target'=>'_blank']).'</br>';
            ?>
        </div>
    </div>
    <div style="clear: both;"></div>
    <div>
        <?php
        $form = ActiveForm::begin([
            'method' => 'post',
            'action' => Url::to(["import/upload"]),
            'options' => ['enctype' => 'multipart/form-data'] // important
        ]);
        echo $form->field($model, 'import_type')->widget(Select2::class,
            ["data" => ImportForm::getTypeList(),
                'options' => ['multiple' => false, 'placeholder' => 'Выберите тип'],
                'language' => 'ru',
                'pluginOptions' => [
                    'allowClear' => true,
                    'width' => '100%'
                ],
            ]);
        echo $form->field($model, 'import_file')->fileInput(['multiple' => false,]);
        echo Html::submitButton("Загрузить", ['class' => 'btn btn-primary']);
        ActiveForm::end();
        ?>
    </div>
</div>
