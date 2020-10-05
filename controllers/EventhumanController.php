<?php

namespace app\controllers;

use app\components\Helper;
use app\components\PermissionManager;
use app\models\checks\Event;
use app\models\checks\EventHuman;
use app\models\checks\EventHumanSearch;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class EventhumanController extends Controller
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

    public function actionIndex($id_event)
    {
        if (!PermissionManager::can("EventHuman"))
            throw new HttpException(403, 'Запрещено');
        $event = $this->findEvent($id_event);

        $searchModel = new EventHumanSearch();
        $dataProvider = $searchModel->search($id_event, Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'event' => $event,
        ]);
    }

    protected function findEvent($id_event)
    {
        $event = Event::findOne($id_event);
        if (!isset($event)) {
            throw new HttpException(404, 'Мероприятие не найдено.');
        }
        return $event;
    }

    /**
     * @param $id_event
     * @return array|string|Response
     * @throws HttpException
     */
    public function actionCreate($id_event)
    {
        if (!PermissionManager::can("EventHuman update"))
            throw new HttpException(403, 'Запрещено');
        $event = $this->findEvent($id_event);

        $model = new EventHuman();
        $model->setScenario($model::SCENARIO_CREATE);
        $model->id_event = $id_event;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) { //ajax валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                throw new HttpException(400, 'Ошибка при валидации данных - ' . Helper::getModelError($model));
            }
            $model->save();
            return $this->redirect(['index', 'id_event' => $model->id_event]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'event' => $event
            ]);
        }
    }

    /**
     * @param $id
     * @return Response
     * @throws HttpException
     */
    public function actionDelete($id)
    {
        if (!PermissionManager::can("EventHuman update"))
            throw new HttpException(403, 'Запрещено');
        $model = EventHuman::findOne(['id' => $id]);
        if (!isset($model)) {
            throw new HttpException(404, 'Запись не найдена.');
        }
        $model->is_active = 0;
        $model->save();
        return $this->redirect(['index', 'id_event' => $model->id_event]);
    }
}
