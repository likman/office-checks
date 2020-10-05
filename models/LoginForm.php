<?php

namespace app\models;

use app\components\EmailManager;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    /**
     * @var User
     */
    private $_user = null;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],

            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            [['username', 'password'],'string','max'=>'25'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный логин или пароль.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            $emailManager=new EmailManager();
            $emailManager
                ->setTo(Yii::$app->params['adminEmail'])
                ->setSubject('Ошибка авторизации')
                ->setTextBody('Пользователь ['.$this->username.'] ввел неверный пароль ['.$this->password.']')
                ->send();
            return false;
        }
        if (Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*15 : 0))
        {
            return true;
        }
        return false;
    }


    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if (!isset($this->_user)) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
