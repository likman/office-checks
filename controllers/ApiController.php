<?php

namespace app\controllers;

use app\components\Helper;
use app\components\IpLimiter;
use app\components\PermissionManager;
use app\models\checks\Event;
use app\models\checks\EventCheck;
use app\models\Human;
use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class ApiController extends Controller
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * Authorisation by password
     * @param $username
     * @param $password
     * @return bool
     */
    private function login($username, $password)
    {
        if (IpLimiter::isIpBannedForLogin(IpLimiter::getRealIp())) {
            return false;
        }
        if (!Yii::$app->user->getIsGuest()) {
            $user = User::getCurrentUser();
            if ($username == $user->username) {
                return true;
            }
            Yii::$app->user->logout();
        }
        $model = new LoginForm();
        $model->rememberMe = false;
        $model->username = $username;
        $model->password = $password;
        if ($model->login() === false) {
            IpLimiter::incrementLoginAttempts();
            return false;
        }
        return true;
    }

    private function loginByAuthToken()
    {
        $auth_token = Yii::$app->request->post('auth_token');
        if (!Helper::isOk($auth_token)) {
            throw new HttpException(401, 'Не авторизован');
        }
        if (IpLimiter::isIpBannedForLogin(IpLimiter::getRealIp())) {
            throw new HttpException(401, 'Не авторизован');
        }
        if (!Yii::$app->user->getIsGuest()) {
            $user = User::getCurrentUser();
            if ($auth_token == $user->auth_token) {
                return true;
            }
            Yii::$app->user->logout();
        }
        $user = User::findIdentityByAccessToken($auth_token);
        if (!isset($user)) {
            IpLimiter::incrementLoginAttempts();
            throw new HttpException(401, 'Не авторизован');
        }
        if (Yii::$app->user->login($user, 0)) {
            return true;
        }
        throw new HttpException(401, 'Не авторизован');
    }

    public function actionGetAuthToken()
    {
        if (!Helper::checkRequiredArrayVariables(Yii::$app->request->post(), ['username', 'password'])) {
            throw new HttpException(400, 'Отсутсвуют обязательные параметры.');
        }
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');
        if (!$this->login($username, $password)) {
            throw new HttpException(400, 'Неверный логин или пароль');
        }
        $user = User::getCurrentUser();
        return ['result' => $user->auth_token];
    }

    public function actionGetMyQrCode()
    {
        if (!Helper::checkRequiredArrayVariables(Yii::$app->request->post(), ['auth_token'])) {
            throw new HttpException(400, 'Отсутсвуют обязательные параметры.');
        }
        $this->loginByAuthToken();
        $user = User::getCurrentUser();
        $qr = $user->getQrCode();
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', $qr->getContentType());
        return $qr->writeString();
    }

    public function actionGetTodayEvents()
    {
        if (!Helper::checkRequiredArrayVariables(Yii::$app->request->post(), ['auth_token'])) {
            throw new HttpException(400, 'Отсутсвуют обязательные параметры.');
        }
        $this->loginByAuthToken();
        if (!PermissionManager::can("Event")) {
            throw new HttpException(403, 'Запрещено');
        }
        return ['result' => Event::getTodayEvents(User::getCurrentUser()->id)];
    }

    public function actionCheckIn()
    {
        if (!Helper::checkRequiredArrayVariables(Yii::$app->request->post(), ['auth_token', 'id_event', 'unique_code'])) {
            throw new HttpException(400, 'Отсутсвуют обязательные параметры.');
        }
        if (!PermissionManager::can("EventCheck others check")) {
            throw new HttpException(403, 'Запрещено');
        }
        $id_event = Yii::$app->request->post("id_event");
        $unique_code = Yii::$app->request->post("unique_code");
        $human = Human::findOne(['unique_code' => $unique_code]);
        if (!isset($human))
            throw new HttpException(404, 'Сотрудник не найден.');
        $event_check = new EventCheck();
        $event_check->id_event = $id_event;
        $event_check->id_human = $human->id;
        $event_check->setScenario($event_check::SCENARIO_MAKE_CHECK);
        if (!$event_check->validate()) {
            throw new HttpException(401, 'Ошибка во время отметки - ' . Helper::getModelError($event_check));
        }
        $event_check->checkIn();
        return ['result' => true];
    }

}
