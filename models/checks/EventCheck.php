<?php

namespace app\models\checks;

use app\components\Helper;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "event_checks".
 *
 * @property integer $id
 * @property integer $id_event
 * @property integer $id_human
 * @property string $check_time_in
 * @property integer $modified_by
 *
 */
class EventCheck extends ActiveRecord
{

    const SCENARIO_MAKE_CHECK = 'create';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'event_checks';
    }

    public static function getUncheckedTodayEventNames($id_human)
    {
        $sql = "select id, name 
              from events
              where events.is_active='1' and events.start_time like :event_date
              and exists (select 1 from event_human_bindings where event_human_bindings.id_event=events.id
              and event_human_bindings.id_human=:id_human and event_human_bindings.is_active='1')
              and not exists (select 1 from event_checks where event_checks.id_event=events.id
              and event_checks.id_human=:id_human2 and event_checks.check_time_in is not null)";
        $params = [
            ':id_human' => $id_human,
            ':id_human2' => $id_human,
            ':event_date' => date('Y-m-d') . '%'
        ];
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();
        return ArrayHelper::map($rows, 'id', 'name');
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['id_event', 'id_human', 'modified_by',], 'integer'],
            [['check_time_in',], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['id_event', 'id_human'], 'required', 'on' => self::SCENARIO_MAKE_CHECK],
            [['id_event'], 'checkIsAlreadyExists', 'on' => self::SCENARIO_MAKE_CHECK],
            [['id_event'], 'checkIsEventCanBeChecked', 'on' => self::SCENARIO_MAKE_CHECK],
            [['id_human'], 'checkIsHumanBindToEvent', 'on' => self::SCENARIO_MAKE_CHECK],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Код',
            'id_event' => 'Код мероприятия',
            'id_human' => 'Код сотрудника',
            'check_time_in' => 'Время прибытия',
            'modified_by' => 'Отметил',
        ];
    }

    public function checkIsAlreadyExists($attribute, $params)
    {
        if (self::hasCheckedIn($this->id_event, $this->id_human)) {
            $this->addError($attribute, 'Такая запись уже есть в базе.');
        }
    }

    public static function hasCheckedIn($id_event, $id_human)
    {
        $event_check = self::findOne(
            [
                'id_event' => $id_event,
                'id_human' => $id_human,
            ]);
        if (isset($event_check->check_time_in)) {
            return true;
        }
        return false;
    }

    public function checkIsEventCanBeChecked($attribute, $params)
    {
        $event = Event::findOne(['id' => $this->id_event]);
        if (!isset($event)) {
            $this->addError($attribute, 'Мероприятие не найдено.');
        }
        $event_date = Helper::convertDate($event->start_time, 'Y-m-d H:i:s', 'Y-m-d');
        if ($event_date != date('Y-m-d')) {
            $this->addError($attribute, 'Дата мероприятия не соответствует сегодняшней.');
        }
    }

    public function checkIsHumanBindToEvent($attribute, $params)
    {
        if (!EventHuman::hasBind($this->id_event, $this->id_human)) {
            $this->addError($attribute, 'Сотрудник не записан на мероприятие.');
        }
    }

    public function checkIn()
    {
        $this->check_time_in = date('Y-m-d H:i:s');
        $this->save();
    }

}
