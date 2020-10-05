<?php

namespace app\controllers;

use app\components\PermissionManager;
use app\models\checks\EventType;
use app\models\checks\EventTypeSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class EventtypeController extends Controller
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
        if (!PermissionManager::can("EventType"))
            throw new HttpException(403, 'Запрещено');
        $searchModel = new EventTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        if (!PermissionManager::can("EventType"))
            throw new HttpException(403, 'Запрещено');
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    protected function findModel($id)
    {
        if (($model = EventType::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Страница не найдена.');
    }

    public function actionCreate()
    {
        if (!PermissionManager::can("EventType update"))
            throw new HttpException(403, 'Запрещено');
        $model = new EventType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        if (!PermissionManager::can("EventType update"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        if (!PermissionManager::can("EventType update"))
            throw new HttpException(403, 'Запрещено');
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
}
