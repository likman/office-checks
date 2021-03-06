<?php

namespace app\components;
use DateTime;
use ParseCsv\Csv;
use Yii;
use yii\base\Model;
use yii\web\Controller;
use yii\web\Request;

class Helper
{

    public static function isEmpty($value)
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }

    public static function checkRequiredArrayVariables($array, $required_variables)
    {
        foreach ($required_variables as $var) {
            if (!Helper::isOk($array[$var])) {
                return false;
            }
        }
        return true;
    }

    public static function isOk($param)
    {
        if (isset($param) && ($param != ""))
            return true;
        else
            return false;
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateRandomNumber($length = 10)
    {
        return random_int(pow(10,$length), pow(10,$length+1));
    }

    public static function getLastMySQLId($db="db")
    {
        return Yii::$app->$db->createCommand("SELECT LAST_INSERT_ID();")->queryScalar();
    }

    public static function redirectPrevious(Controller $c, $method = "POST")
    {
        if ($method == "GET") {
            return $c->redirect(Yii::$app->request->referrer);
        }
        $request = new Request(['url' => parse_url(Yii::$app->request->referrer, PHP_URL_PATH)]);
        $url = Yii::$app->urlManager->parseRequest($request);
        return $c->redirect($url);
    }

    public static function getClassName($classname)
    {
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }
        return $classname;
    }

    public static function getClassNameFromObject($object)
    {
        $class = get_class($object);
        if ($pos = strrpos($class, '\\')) {
            return substr($class, $pos + 1);
        }
        return $class;
    }


    public static function getDataFromCsvFile($file)
    {
        $file_data = file_get_contents($file);
        $encoding = mb_detect_encoding($file_data, mb_detect_order(), true);
        if ($encoding === false) {
            $encoding = 'cp1251';
        }
        if ($encoding == 'UTF-8') {
            file_put_contents($file, str_replace("\xEF\xBB\xBF", '', $file_data));
        }
        $csv = new Csv();
        if ($encoding!==false && $encoding!='UTF-8') {
            $csv->encoding($encoding, 'UTF-8');
        }
        $csv->auto($file);
        return $csv->data;
    }

    public static function arrayToCsv($array, $convert_header_labels=false, $labels=[])
    {
        $csv = new ECSVExport($array);
        $encode = "\xEF\xBB\xBF";
        $csv->setDelimiter(';');
        if ($convert_header_labels && count($array)>0) {
            foreach ($array[0] as $header => $val) {
                if (isset($labels[$header])) {
                    $csv->setHeader($header, $labels[$header]);
                }
            }
        }
        return $encode.$csv->toCSV();
    }

    /**
     * @param $model
     * @return mixed
     */
    public static function getModelError(Model $model)
    {
        return array_shift($model->getFirstErrors());
    }

    public static function deleteDirectory($path)
    {
        if (is_dir($path) === true)
        {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file)
            {
                Helper::deleteDirectory(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }

        else if (is_file($path) === true)
        {
            chmod($path,0777);
            return unlink($path);
        }

        return false;
    }

    public static function makeColumnsAdaptive($columns, $labels)
    {
        $adaptive_columns=[];
        foreach ($columns as $column) {
            $c=[];
            $label='';
            $can_be_adaptive=true;
            if (is_array($column)) {
                $c=$column;
                if (!isset($column['attribute'])) {
                    $can_be_adaptive=false;
                }
                if ($can_be_adaptive) {
                    if (isset($column['label'])) {
                        $label = $column['label'];
                    } else {
                        $label = $labels[$column['attribute']];
                        if (!isset($label)) {
                            $label=$column['attribute'];
                        }
                    }
                }
            } else {
                $c['attribute']=$column;
                $label=$labels[$column];
                if (!isset($label)) {
                    $label=$column;
                }
            }
            if ($can_be_adaptive) {
                $c['contentOptions'] = function ($model) use ($label) {
                    return ['data-label' => $label];
                };
            }
            $adaptive_columns[]=$c;
        }
        return $adaptive_columns;
    }


    /**
     * Create an array of dates from start_date to end_date
     * @param $start_date
     * @param $end_date
     * @param $in_format
     * @param $out_format
     * @return array dates
     */
    public static function getArrayOfDatesFromPeriod($start_date, $end_date, $in_format='Y-m-d', $out_format='Y-m-d')
    {
        $dt_start_date = DateTime::createFromFormat($in_format, $start_date);
        $days_count = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
        $day = new DateTime($dt_start_date->format($in_format));
        $dates = [];
        for ($i=0;$i<=$days_count;$i++) {
            $dates[]= $day->format($out_format);
            date_add($day, date_interval_create_from_date_string('1 days'));
        }
        return $dates;
    }

    public static function incrementDate($date, $count_days) {
        $day = new DateTime($date);
        date_add($day, date_interval_create_from_date_string($count_days . ' days'));
        return $day->format('Y-m-d');
    }

    public static function decrementDate($date, $count_days) {
        $day = new DateTime($date);
        date_sub($day, date_interval_create_from_date_string($count_days . ' days'));
        return $day->format('Y-m-d');
    }

    public static function isDatePeriodOk($start_date, $end_date, $max_days_count)
    {
        $dt_start_date = DateTime::createFromFormat('Y-m-d', $start_date);
        $dt_end_date = DateTime::createFromFormat('Y-m-d', $end_date);
        date_sub($dt_end_date, date_interval_create_from_date_string($max_days_count . ' days'));
        if ($dt_end_date->format('Y-m-d') > $dt_start_date->format('Y-m-d')) {
            return false;
        }
        return true;
    }

    public static function convertDate($in_date, $in_format, $out_format)
    {
        $date = DateTime::createFromFormat($in_format, $in_date);
        return $date->format($out_format);
    }

    public static function isDateBetweenTwoDates($check_date, $first_date, $last_date)
    {
        $cdate = new DateTime($check_date); // Today
        $date1 = new DateTime($first_date);
        $date2 = new DateTime($last_date);

        if (
            $cdate->getTimestamp() >= $date1->getTimestamp() &&
            $cdate->getTimestamp() <= $date2->getTimestamp()) {
            return true;
        }
        return false;
    }


}