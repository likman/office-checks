<?php
namespace app\components;
use app\models\User;
use Exception;
use Yii;

class Logger
{
    static $prepare;

    public static function prepare()
    {
        try {
            if (!isset(Logger::$prepare) || Logger::$prepare == "") {
                $id_human = User::getCurrentUser()->id;
                $name = User::getCurrentUser()->name;
                if (isset ($id_human) && isset($name))
                    Logger::$prepare = '[' . $name . '][' . $id_human . "] ";
                else
                    Logger::$prepare = "";
            }
            return Logger::$prepare;
        } catch (Exception $e) {
        }
        return "";
    }

    public static function error($message, $category='')
    {
        Yii::error(Logger::prepare().$message,$category);
    }

    public static function info($message, $category='')
    {
        Yii::info(Logger::prepare().$message,$category);
    }

    public static function warning($message, $category='')
    {
        Yii::warning(Logger::prepare().$message,$category);
    }
}