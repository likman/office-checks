<?php

namespace app\models;


use app\components\Helper;
use Yii;

class HumanForm extends Human
{
    public $password;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['password', 'string'];
        $rules[] = ['password', 'required', 'on' => self::SCENARIO_CREATE];
        return $rules;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['password'] = 'Новый пароль';
        return $labels;
    }

    public function loadDefaults()
    {
        parent::loadDefaults();
        $this->password = Helper::generateRandomNumber(8);
    }

    public function beforeSave($insert)
    {
        if (Helper::isOk($this->password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }
        return parent::beforeSave($insert);
    }


}