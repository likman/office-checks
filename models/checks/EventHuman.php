<?php

namespace app\models\checks;

use app\models\User;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "event_human_bindings".
 *
 * @property integer $id
 * @property integer $id_event
 * @property integer $id_human
 * @property string $created_at
 * @property string $modified_at
 * @property boolean $is_active
 * @property integer $modified_by
 *
 */
class EventHuman extends ActiveRecord
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'event_human_bindings';
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['id', 'id_event', 'id_human', 'modified_by'], 'integer'],
            [['id_event', 'id_human',], 'required', 'on' => self::SCENARIO_CREATE],
            [['id_event'], 'checkIsAlreadyExists', 'on' => self::SCENARIO_CREATE],
            [['id'], 'required', 'on' => self::SCENARIO_DELETE],
        ];
    }


    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id_event' => 'Мероприятие',
            'id' => 'Код',
            'id_human' => 'Сотрудник',
            'created_at' => 'Время добавления',
            'modified_at' => 'Время модификации',
            'is_active' => 'Активен',
            'modified_by' => 'Изменил',
        ];
    }

    public function checkIsAlreadyExists($attribute, $params)
    {
        if (self::hasBind($this->id_event, $this->id_human)) {
            $this->addError($attribute, 'Такая запись уже есть в базе.');
        }
    }

    public static function hasBind($id_event, $id_human)
    {
        $event_human = self::findOne(
            [
                'id_event' => $id_event,
                'id_human' => $id_human,
                'is_active' => '1'
            ]);
        if (isset($event_human)) {
            return true;
        }
        return false;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        } else {
            $this->modified_at = date('Y-m-d H:i:s');
        }
        $this->modified_by = User::getCurrentUser()->id;
        return parent::beforeSave($insert);
    }

}
