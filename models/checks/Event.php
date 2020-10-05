<?php

namespace app\models\checks;

use app\components\Helper;
use app\models\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "events".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $start_time
 * @property integer $id_event_type
 * @property string $created_at
 * @property string $modified_at
 * @property boolean $is_active
 * @property integer $modified_by
 *
 */
class Event extends ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'events';
    }

    public static function getTodayEvents($id_human)
    {
        $sql = "select events.id, events.name, events.description, events.start_date, 
                events.id_event_type, event_types.name as event_type_name
              from events
              left join event_types on event_types.id=events.id_event_type
              where events.is_active='1' and exists (select 1 from event_human_bindings
             where event_human_bindings.id_human=:id_human and event_human_bindings.id_event=events.id 
             and event_human_bindings.is_active='1')
             and events.start_time like :event_date
                                           ";
        $params = [
            ':id_human' => $id_human,
            ':event_date' => date('Y-m-d') . '%'
        ];
        return Yii::$app->db->createCommand($sql, $params)->queryAll();
    }

    public static function getList()
    {
        $sql = "select id, name from events
              where is_active='1'
              order by id desc";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['id', 'id_event_type', 'modified_by'], 'integer'],
            ['is_active', 'boolean'],
            [['name', 'description',], 'string'],
            ['start_time', 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['name', 'id_event_type', 'start_time',], 'required', 'on' => self::SCENARIO_CREATE],
            [['id', 'name', 'id_event_type', 'start_time',], 'required', 'on' => self::SCENARIO_UPDATE],
            [['id'], 'required', 'on' => self::SCENARIO_DELETE],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Код мероприятия',
            'name' => 'Мероприятие',
            'description' => 'Доп. информация',
            'id_event_type' => 'Тип мероприятия',
            'start_time' => 'Время начала',
            'created_at' => 'Время добавления',
            'modified_at' => 'Время модификации',
            'is_active' => 'Активен',
            'modified_by' => 'Изменил',
        ];
    }

    public function loadDefaults()
    {
        $this->id_event_type = 1;
        $this->start_time = date("Y-m-d H:i:s");
    }

    public static function hasAnyEvent($date)
    {
        $sql = "select id from events
              where is_active='1' and start_time like :event_date";
        $event = Yii::$app->db->createCommand($sql, [':event_date' => $date . '%'])->queryOne();
        if (Helper::isOk($event['id'])) {
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
