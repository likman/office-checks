<?php

namespace app\controllers;

use app\components\Helper;
use app\components\PermissionManager;
use app\models\checks\Event;
use app\models\checks\EventSearch;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class EventController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
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

    public function actionIndex()
    {
        if (!PermissionManager::can('Event'))
            throw new HttpException(403, 'Запрещено');
        $searchModel = new EventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        if (!PermissionManager::can('Event update'))
            throw new HttpException(403, 'Запрещено');
        $model = new Event();
        $model->setScenario($model::SCENARIO_CREATE);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) { //ajax валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                return $this->render('create', [
                    'model' => $model,
                    'error_message' => 'Ошибка при валидации данных - ' . Helper::getModelError($model)
                ]);
            }
            $model->save();
            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            $model->loadDefaults();
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        if (!PermissionManager::can("Event update"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        $model->setScenario($model::SCENARIO_UPDATE);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) { //ajax валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                return $this->render('create', [
                    'model' => $model,
                    'error_message' => 'Ошибка при валидации данных - ' . Helper::getModelError($model)
                ]);
            }
            $model->save();
            return $this->render('update', [
                'model' => $model,
                'success_message' => 'Успешно отредактировано.'
            ]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    public function actionView($id)
    {
        if (!PermissionManager::can("Event"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        if (!PermissionManager::can("Event update"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        $model->is_active = 0;
        $model->save();
        return Helper::redirectPrevious($this, "GET");
    }

    public function actionRestore($id)
    {
        if (!PermissionManager::can("Event update"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        $model->is_active = 1;
        $model->save();

        return Helper::redirectPrevious($this, "GET");
    }


    protected function findModel($id)
    {
        if (!Helper::isOk($id))
            return null;
        $model = Event::findOne(['id' => $id]);
        if (!isset($model)) {
            throw new HttpException(404, 'Мероприятие не найдено.');
        }
        return $model;
    }
}
