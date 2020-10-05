<?php

namespace app\models;

use app\components\Helper;
use app\components\QrCodeHelper;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "humans".
 *
 * @property integer $id
 * @property string $name
 * @property string $telephone
 * @property string $email
 * @property integer $id_role
 * @property string $work_time_start
 * @property string $work_time_end
 * @property string $unique_code
 * @property string $auth_token
 * @property string $password_hash
 * @property string $created_at
 * @property string $modified_at
 * @property boolean $is_active
 * @property integer $modified_by
 *
 */
class Human extends ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'humans';
    }

    public static function getHumanByUniqueCode($unique_code)
    {
        if (!Helper::isOk($unique_code))
            return null;
        $sql = "SELECT * FROM HUMANS WHERE unique_code=:unique_code ";
        $row = Yii::$app->db->createCommand($sql)
            ->bindValues([":unique_code" => $unique_code])->queryOne();
        if ($row === false)
            return null;
        $human = new Human();
        $attributes = $human->getAttributes();
        foreach ($attributes as $attribute => $values) {
            if (isset($row[$attribute]))
                $human->$attribute = $row[$attribute];
        }
        return $human;
    }

    public static function getList()
    {
        $sql = "select id, name 
              from humans
              where is_active='1'
              order by name asc";
        $array = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($array as &$arr) {
            $arr['name'] = $arr['id'] . ', ' . $arr['name'];
        }
        return $array;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['id', 'id_role', 'telephone', 'modified_by',], 'integer'],
            ['is_active', 'boolean'],
            [['name', 'password_hash', 'unique_code',], 'string'],
            [['work_time_start', 'work_time_end'], 'date', 'format' => 'php:H:i'],
            ['email', 'email'],
            [['name', 'id_role', 'telephone', 'work_time_start', 'work_time_end',], 'required', 'on' => self::SCENARIO_CREATE],
            ['telephone', "checkIsTelephoneAlreadyRegistered", 'on' => self::SCENARIO_CREATE],
            ['telephone', "checkIsTelephoneAlreadyRegistered", 'on' => self::SCENARIO_UPDATE],
            [['id', 'id_role', 'telephone', 'work_time_start', 'work_time_end',], 'required', 'on' => self::SCENARIO_UPDATE],
            [['id'], 'required', 'on' => self::SCENARIO_DELETE],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Код',
            'name' => 'Сотрудник',
            'email' => 'Почта',
            'telephone' => 'Телефон',
            'id_role' => 'Роль',
            'work_time_start' => 'Время начала работы',
            'work_time_end' => 'Время окончания работы',
            'created_at' => 'Время добавления',
            'modified_at' => 'Время модификации',
            'is_active' => 'Активен',
            'modified_by' => 'Изменил',
        ];
    }

    public function loadDefaults()
    {
        $this->id_role = 1;
        $this->work_time_start = '09:00';
        $this->work_time_end = '18:00';
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
            $this->generateAuthToken();
            $this->generateUniqueCode();
        } else {
            $this->modified_at = date('Y-m-d H:i:s');
        }
        $this->modified_by = User::getCurrentUser()->id;
        return parent::beforeSave($insert);
    }

    public function generateAuthToken()
    {
        $this->auth_token = Yii::$app->security->generateRandomString();
    }

    public function generateUniqueCode()
    {
        $this->unique_code = Yii::$app->security->generateRandomString();
    }

    public function checkIsHumanAlreadyExists($attribute, $params)
    {
        if ($this->isHumanAlreadyExists()) {
            $this->addError($attribute, 'Такой человек уже есть в базе.');
        }
    }

    public function isHumanAlreadyExists()
    {
        $params['telephone'] = $this->telephone;
        $count = Yii::$app->db->createCommand("SELECT COUNT(ID) FROM HUMANS WHERE TELEPHONE=:telephone")
            ->bindValues($params)->queryScalar();
        if ($count > 0) {
            return true;
        } else
            return false;
    }

    public function checkIsTelephoneAlreadyRegistered($attribute, $params)
    {
        if ($this->isTelephoneAlreadyRegistered()) {
            $this->addError($attribute, 'Телефон уже зарегистрирован на другого человека.');
        }
    }

    public function isTelephoneAlreadyRegistered()
    {
        $params['telephone'] = $this->telephone;
        $sql = "SELECT COUNT(ID) FROM HUMANS WHERE TELEPHONE=:telephone";
        if (Helper::isOk($this->id)) {
            $sql .= " AND ID<>:id";
            $params['id'] = $this->id;
        }
        $count = Yii::$app->db->createCommand($sql)
            ->bindValues($params)->queryScalar();
        if ($count > 0)
            return true;
        else
            return false;
    }

    public function getQrCode()
    {
        if (!Helper::isOk($this->id))
            return false;
        return QrCodeHelper::getQrCode($this->unique_code);
    }


}
