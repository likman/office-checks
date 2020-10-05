<?php

namespace app\models;

use app\components\EmailManager;
use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $name;
    public $position;
    public $email;
    public $body;
    public $verifyCode;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'body', 'position'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Защита от роботов',
            'name' => 'ФИО',
            'email' => 'Почта',
            'body' => 'Текст обращения',
            'position' => 'Должность',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return boolean whether the model passes validation
     */
    public function contact($email)
    {
        if ($this->validate()) {
            $text='ФИО: '.$this->name."\r\n";
            $text.='Email: '.$this->email."\r\n";
            $text.='Должность: '.$this->position."\r\n";
            $text.='Текст обращения: '.$this->body."\r\n";
            EmailManager::build()->setTo($email)
                ->setFrom(Yii::$app->params['robotEmail'])
                ->setSubject('Обращение с ' . Yii::$app->name)
                ->setTextBody($text)
                ->send();
            return true;
        }
        return false;
    }
}
