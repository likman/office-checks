<?php

namespace app\controllers;

use app\components\Helper;
use app\components\PermissionManager;
use app\models\checks\Event;
use app\models\checks\EventCheck;
use app\models\checks\EventCheckSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;

class EventcheckController extends Controller
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

    /**
     * @param $id_event
     * @return string
     * @throws HttpException
     */
    public function actionIndex($id_event)
    {
        if (!PermissionManager::can("EventCheck"))
            throw new HttpException(403, 'Запрещено');
        $searchModel = new EventCheckSearch();
        $dataProvider = $searchModel->search($id_event, Yii::$app->request->queryParams);

        $event = $this->findEvent($id_event);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'event' => $event,
        ]);
    }

    /**
     * Check in event
     * @param $id_event
     * @param $id_human
     * @throws HttpException
     */
    public function actionCheckin($id_event, $id_human)
    {
        if (!PermissionManager::can("EventCheck self check"))
            throw new HttpException(403, 'Запрещено');
        if (!PermissionManager::can("EventCheck others check") && User::getCurrentUser()->id != $id_human) {
            throw new HttpException(403, 'Запрещено');
        }
        $model = new EventCheck();
        $model->id_event = $id_event;
        $model->id_human = $id_human;
        $model->setScenario(EventCheck::SCENARIO_MAKE_CHECK);
        if (!$model->validate())
            throw new HttpException(400, 'Ошибка при валидации данных - ' . Helper::getModelError($model));
        $model->checkIn();
        Helper::redirectPrevious($this, "GET");
    }

    protected function findEvent($id_event)
    {
        $event = Event::findOne($id_event);
        if (!isset($event)) {
            throw new HttpException(404, 'Мероприятие не найдено.');
        }
        return $event;
    }

}
