<?php
namespace app\components\auto;

use app\components\EmailManager;
use app\components\Helper;
use app\models\stewards\Event;
use app\models\stewards\EventBindStatus;
use app\models\stewards\EventStewards;
use Yii;

class GenerateEvents
{
    private $_start_date;
    private $_end_date;

    private function getDatesWithoutWeekends(): array
    {
        $dt_start_date= \DateTime::createFromFormat('Y-m-d', $this->_start_date);
        $days_count=(strtotime($this->_end_date)-strtotime($this->_start_date))/(60*60*24);
        $day=new \DateTime($dt_start_date->format('Y-m-d'));
        $dates=[];
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
        $this->_start_date=$start_date;
        $this->_end_date=$end_date;

        $dates=$this->getDatesWithoutWeekends();

        $transaction=Yii::$app->db->beginTransaction();
        $sql="select b_event_humans.id_human
              from b_event_humans
              where b_event_humans.id_event_bind_status='".EventBindStatus::BIND_OK."'
              and b_event_humans.id_event=(SELECT MAX(r_events.id_event)
    FROM r_events WHERE r_events.deleted='0' and r_events.event_date < :start_date)";
        $humans=Yii::$app->db->createCommand($sql,[':start_date'=>$start_date])->queryAll();
        foreach ($dates as $date)
        {
            if (Event::isExists($date)) {
                continue;
            }
            $event=new Event();
            $event->event_date=$date;
            $event->event_name=Helper::convertDate($date, 'Y-m-d','d.m.Y');
            $event->id_place=2;
            $event->event_time='09:00:00';
            $event->modified_by=1;
            $event->save();
            foreach ($humans as $human) {
                $binding=new EventStewards();
                $binding->id_human=$human['id_human'];
                $binding->id_event=$event->id_event;
                $binding->id_event_bind_status=EventBindStatus::BIND_OK;
                $binding->saveWithoutChecks();
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
