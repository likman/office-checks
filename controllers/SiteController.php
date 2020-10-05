<?php

namespace app\controllers;

use app\components\Helper;
use app\components\IpLimiter;
use app\components\MikrotikRdpAllower;
use app\components\PermissionManager;
use app\models\checks\EventCheck;
use app\models\ContactForm;
use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class SiteController extends Controller
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
                        'actions' => ['login', 'contact', 'captcha', 'error', 'offline'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            /*  'rateLimiter' => [
                  'class' => RateLimiter::className(),
                  'user' => new \app\components\IpLimiter(),
                  'except' => ['error'],
              ],*/
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $event_for_check = null;
        $id_human = User::getCurrentUser()->id;
        $events_for_check = [];
        if (PermissionManager::can("EventCheck self check")) {
            $events_for_check = EventCheck::getUncheckedTodayEventNames($id_human);
        }
        return $this->render('index', [
            'events_for_check' => $events_for_check,
            'id_human' => $id_human
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if (IpLimiter::isIpBannedForLogin(IpLimiter::getRealIp())) {
            die('Вы заблокированы.');
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                return $this->goBack();
            } else {
                if (IpLimiter::incrementLoginAttempts()) {
                    die('Вы заблокированы.');
                }
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Allow user to use RDP
     * @return string
     */
    public function actionRdpauth()
    {
        $port = Yii::$app->request->post('secret');
        if (!Helper::isOk($port)) {
            return $this->render('rdpauth', [

            ]);
        }
        if (!is_numeric($port)) {
            return $this->render('rdpauth', [
                'error_message' => 'Введите верный код'
            ]);
        }
        if (!MikrotikRdpAllower::allowConnectionToPort($port)) {
            return $this->render('rdpauth', [
                'error_message' => 'Ошибка при выполнении запроса... Убедитесь, что ввели верный порт.'
            ]);
        }
        return $this->render('rdpauth', [
            'success_message' => 'Доступ разрешен'
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionOffline()
    {
        return $this->render('offline');
    }


}
