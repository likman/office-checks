<?php
namespace app\components\auto;

use app\components\EmailManager;
use app\components\Helper;
use app\models\stewards\Human;
use Yii;

class ExportChecks
{
    private $_start_date;
    private $_end_date;

    private function getHuman($id_human)
    {
        $human=new Human();
        $human->id_human=$id_human;
        $human->loadById();
        return $human;
    }

    private function getWorkTime($check_time_in, Human $human)
    {
        $check_time = Helper::convertDate($check_time_in, 'Y-m-d H:i:s', 'H:i');

        $time = explode(':', $human->work_time_start);
        $work_time_start_minutes = $time[0]*60 + $time[1];

        $time = explode(':', $human->work_time_end);
        $work_time_end_minutes = $time[0]*60 + $time[1];

        if (($work_time_end_minutes - $work_time_start_minutes) >= 6*60) {
            $has_dinner_time = true;
        } else {
            $has_dinner_time = false;
        }

        $time = explode(':', $check_time);
        $check_time_minutes = $time[0]*60 + $time[1];

        if ($check_time_minutes > $work_time_start_minutes) {
            $minutes_in_work = $work_time_end_minutes - $check_time_minutes;
        } else {
           $minutes_in_work = $work_time_end_minutes - $work_time_start_minutes;
        }

        if ($has_dinner_time) {
            $minutes_in_work -= 60;
        }

        $hours = floor($minutes_in_work/ 60);
        $minutes = $minutes_in_work % 60;
        return $hours.':'.$minutes;
    }

    public function doWork($start_date, $end_date)
    {
        $this->_start_date=$start_date;
        $this->_end_date=$end_date;
        $dates=Helper::getArrayOfDatesFromPeriod($start_date, $end_date);
        $data=[];
        $d=[];
        $sql="select r_events.id_event, r_events.event_date, r_event_checks.check_time_in, r_event_checks.id_human
              from r_events
              left join r_event_checks on r_event_checks.id_event=r_events.id_event
              where r_events.deleted='0' and r_events.event_date between :start_date and :end_date
              and not exists (select 1 from s_humans 
              where s_humans.id_post='4' and s_humans.id_human=r_event_checks.id_human)
              order by r_event_checks.id_human asc, r_events.event_date asc";
        $rows=Yii::$app->db->createCommand($sql,[':start_date'=>$start_date, ':end_date'=>$end_date])->queryAll();
        $id_human='';
        $human=null;
        foreach ($rows as $row)
        {
            if ($id_human!=$row['id_human']) {
                if ($id_human!='') {
                    $data[]=$d;
                }
                $id_human=$row['id_human'];
                $human=$this->getHuman($row['id_human']);
                $d = [];
                $d['human_name'] = $human->human_name;
                foreach ($dates as $date) {
                    $d[$date] = '';
                }
            }
            $d[$row['event_date']]=$this->getWorkTime($row['check_time_in'], $human);
        }
        if ($id_human!='') {
            $data[]=$d;
        }
        $labels=[
            'human_name'=>'Сотрудник',
        ];
        foreach ($dates as $date) {
            $labels[$date]=Helper::convertDate($date,'Y-m-d','d.m.Y');
        }
        unset($rows);
        $csv=Helper::arrayToCsv($data, true,  $labels);
        $this->sendReport($csv);
    }

    private function sendReport($csv)
    {
        $text='Выгрузка за даты '
            .Helper::convertDate($this->_start_date,'Y-m-d','d.m.Y')
            .' - '
            .Helper::convertDate($this->_end_date,'Y-m-d','d.m.Y')
            .' во вложении.';
        EmailManager::build()
            ->setFrom(Yii::$app->params['robotEmail'])
            ->setTo(['office@nsteam.ru',
                    'kabanov@nsteam.ru'])
            ->setSubject('Выгрузка по рабочему времени')
            ->setTextBody($text)
            ->attachContent($csv, ['fileName' => 'report.csv',])
            ->send();
    }
}
