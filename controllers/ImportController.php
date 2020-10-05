<?php

namespace app\controllers;

use app\components\helper;
use app\components\PermissionManager;
use app\models\ImportForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\UploadedFile;

class ImportController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        if (!PermissionManager::can("Import"))
            throw new HttpException(403, 'Запрещено');
        $model = new ImportForm();
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionUpload()
    {
        if (!PermissionManager::can("Import"))
            throw new HttpException(403, 'Запрещено');
        $model = new ImportForm();
        if (!Yii::$app->request->isPost) {
            throw new HttpException(400, 'Запрос не POST');
        }
        if (!$model->load(Yii::$app->request->post())) {
            throw new HttpException(400, 'Неправильно переданы параметры');
        }
        $model->import_file = UploadedFile::getInstance($model, 'import_file');
        if (!$model->validate()) {
            throw new HttpException(400, 'Ошибка при валидации данных - ' . Helper::getModelError($model));
        }
        if (!$model->save()) {
            throw new HttpException(500, 'Ошибка импорта - ' . Helper::getModelError($model));
        }
        return $this->render('index', [
            'model' => $model,
            'success_message' => 'Данные успешно импортированы.'
        ]);
    }
}