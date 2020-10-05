<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\auto\ExportChecks;
use app\components\auto\GenerateEvents;
use yii\console\Controller;

class AutoController extends Controller
{

    public function actionExportchecks()
    {
        $start_date=date('Y-m-d', strtotime("first day of -1 month"));
        $end_date=date('Y-m-d', strtotime("last day of -1 month"));
       $model=new ExportChecks();
       $model->doWork($start_date, $end_date);
    }

    public function actionGenerateevents()
    {
        $start_date=date('Y-m-d', strtotime("first day of +1 month"));
        $end_date=date('Y-m-d', strtotime("last day of +1 month"));
        $model=new GenerateEvents();
        $model->doWork($start_date, $end_date);
    }

}
