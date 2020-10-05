<?php

namespace app\controllers;

use app\components\Helper;
use app\components\PermissionManager;
use app\models\Human;
use app\models\HumanForm;
use app\models\HumanSearch;
use app\models\User;
use Da\QrCode\Exception\UnknownWriterException;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class HumanController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
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
     * @return string
     * @throws HttpException
     */
    public function actionIndex()
    {
        if (!PermissionManager::can("Human"))
            throw new HttpException(403, 'Запрещено');
        $searchModel = new HumanSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return array|string|Response
     * @throws HttpException
     */
    public function actionCreate()
    {
        if (!PermissionManager::can("Human update"))
            throw new HttpException(403, 'Запрещено');
        $model = new HumanForm();
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

    /**
     * @param $id
     * @return array|string
     * @throws HttpException
     */
    public function actionUpdate($id)
    {
        if (!PermissionManager::can("Human update"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        if (!isset($model))
            throw new HttpException(400, 'Не удалось загрузить данные сотрудника.');
        $model->setScenario($model::SCENARIO_UPDATE);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) { //ajax валидация
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate())
                return $this->render('update', [
                    'model' => $model,
                    'error_message' => 'Ошибка при валидации данных - ' . Helper::getModelError($model)
                ]);
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

    /**
     * @return string
     * @throws UnknownWriterException
     */
    public function actionGetmyqrcode()
    {
        $user = User::getCurrentUser();
        $qr = $user->getQrCode();
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', $qr->getContentType());
        return $qr->writeString();
    }

    /**
     * @param $id
     * @return string
     * @throws HttpException
     * @throws UnknownWriterException
     */
    public function actionGetqrcode($id)
    {
        if (!PermissionManager::can("Human delete"))
            throw new HttpException(403, 'Запрещено');
        $human = $this->findModel($id);
        $qr = $human->getQrCode();
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', $qr->getContentType());
        return $qr->writeString();
    }


    /**
     * @param $id
     * @return string
     * @throws HttpException
     */
    public function actionView($id)
    {
        if (!PermissionManager::can("Human"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        $model->setScenario(Human::SCENARIO_UPDATE);
        if (!isset($model))
            throw new HttpException(400, 'Не удалось загрузить данные сотрудника.');
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws HttpException
     */
    public function actionDelete($id)
    {
        if (!PermissionManager::can("Human update"))
            throw new HttpException(403, 'Запрещено');
        $model = $this->findModel($id);
        $model->is_active = 0;
        $model->save();
        return Helper::redirectPrevious($this, "GET");
    }

    /**
     * @param $id
     * @return Response
     * @throws HttpException
     */
    public function actionRestore($id)
    {
        if (!PermissionManager::can("Human"))
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
        $model = HumanForm::findOne($id);
        if (!isset($model)) {
            return null;
        }
        return $model;
    }
}
