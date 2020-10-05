<?php
namespace app\components\auto;

use app\components\EmailManager;
use app\components\Helper;
use app\models\checks\Event;
use app\models\checks\EventHuman;
use DateTime;
use Yii;

class GenerateEvents
{
    private $_start_date;
    private $_end_date;

    private function getDatesWithoutWeekends(): array
    {
        $dt_start_date = DateTime::createFromFormat('Y-m-d', $this->_start_date);
        $days_count = (strtotime($this->_end_date) - strtotime($this->_start_date)) / (60 * 60 * 24);
        $day = new DateTime($dt_start_date->format('Y-m-d'));
        $dates = [];
        for ($i=0;$i<=$days_count;$i++) {
            $dayofweek=$day->format('w');
            if ($dayofweek==6 || $dayofweek==0) {
                date_add($day, date_interval_create_from_date_string('1 days'));
                continue;
            }
            $dates[]= $day->format('Y-m-d');
            date_add($day, date_interval_create_from_date_string('1 days'));
        }
        return $dates;
    }

    public function doWork($start_date, $end_date)
    {
        $this->_start_date = $start_date;
        $this->_end_date = $end_date;

        $dates = $this->getDatesWithoutWeekends();

        $transaction = Yii::$app->db->beginTransaction();
        $sql = "select event_human_bindings.id_human
              from event_human_bindings
              where event_human_bindings.is_active='1' and 
              event_human_bindings.id_event=(SELECT MAX(events.id)
    FROM events WHERE events.is_active='1' and events.start_time < :start_date)";
        $humans = Yii::$app->db->createCommand($sql, [':start_date' => $start_date])->queryAll();
        foreach ($dates as $date) {
            if (Event::hasAnyEvent($date)) {
                continue;
            }
            $event = new Event();
            $event->start_time = $date . ' 09:00:00';
            $event->name = Helper::convertDate($date, 'Y-m-d', 'd.m.Y');
            $event->id_event_type = 1;
            $event->save();
            foreach ($humans as $human) {
                $binding = new EventHuman();
                $binding->id_human = $human['id_human'];
                $binding->id_event = $event->id;
                $binding->save();
            }
        }
        $transaction->commit();
        $this->sendReport();
    }

    private function sendReport()
    {
        $text='Созданы мероприятия за даты '
            .Helper::convertDate($this->_start_date,'Y-m-d','d.m.Y')
            .' - '
            .Helper::convertDate($this->_end_date,'Y-m-d','d.m.Y');
        EmailManager::build()
            ->setFrom(Yii::$app->params['robotEmail'])
            ->setTo(Yii::$app->params['adminEmail'])
            ->setSubject('Созданы мероприятия')
            ->setTextBody($text)
            ->send();
    }
}
