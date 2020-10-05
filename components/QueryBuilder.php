<?php

namespace app\components;

use Yii;
use yii\base\Model;

/**
 * Homegrown querybuilder
 * Class QueryBuilder
 * @package app\components
 */
class QueryBuilder
{
    private $_db;
    private $_sql;
    private $_params = [];
    private $_cache_duration;
    private $_order_by;
    private $_group_by;

    public function __construct($db = 'db')
    {
        $this->_db = $db;
    }

    public static function build($db = 'db')
    {
        return (new self($db));
    }

    public function setParamsFromModel(Model $model, $filter_param_names = [])
    {
        $filter_params = (count($filter_param_names) > 0) ? true : false;
        foreach ($model->attributes() as $name) {
            if ($filter_params && !in_array($name, $filter_param_names)) {
                continue;
            }
            if ($model->$name != '') {
                $this->_params[$name] = $model->$name;
            }
        }
        return $this;
    }

    public function appendSql($sql)
    {
        $this->_sql .= ' ' . $sql;
        return $this;
    }

    public function filterByModel($model, $params)
    {
        foreach ($params as $param) {
            $this->filterByModelField($model, $param[0], $param[1], $param[2]);
        }
        return $this;
    }

    public function filterByModelField($model, $model_field_name, $sql_field_name = '', $operand = '=')
    {
        if (!Helper::isOk($operand)) {
            $operand = '=';
        }
        if (!Helper::isOk($sql_field_name)) {
            $sql_field_name = mb_strtoupper($model_field_name);
        }
        if (is_array($model->$model_field_name)) {
            return $this->filterWhereByArray($model, $model_field_name, $sql_field_name);
        } else {
            if (!Helper::isOk($model->$model_field_name) || trim($model->$model_field_name) == '') {
                return $this;
            }
            switch ($operand) {
                case '=':
                    $this->_sql .= ' AND ' . $sql_field_name . '=:' . $model_field_name;
                    $this->appendParams([':' . $model_field_name => $model->$model_field_name]);
                    break;
                case 'like':
                    $this->_sql .= ' AND ' . $sql_field_name . " LIKE CONCAT('%',:" . $model_field_name . ",'%')";
                    $this->appendParams([':' . $model_field_name => mb_strtolower($model->$model_field_name)]);
                    break;
            }
        }
        return $this;
    }

    private function filterWhereByArray($model, $model_field_name, $sql_field_name = '')
    {
        if (count($model->$model_field_name) == 0) {
            return $this;
        }
        $db = $this->_db;
        $model_class = Helper::getClassName(get_class($model));
        $values = '';
        foreach ($model->$model_field_name as $value) {
            if ($value == '') {
                continue;
            }
            $values .= $value . '#';
        }
        if ($values != '') {
            $sql = "INSERT INTO TEMP_VALUES_LIST (FIELD_TYPE,FIELD_VALUE)
                  SELECT '" . $model_field_name . "-" . $model_class . "',RESULTVAL FROM GET_STRING_LIST(?)
                  ";
            $p = [1 => $values];
            Yii::$app->$db->createCommand($sql)->bindValues($p)->execute();
            $this->_sql .= " AND EXISTS (SELECT 1 FROM TEMP_VALUES_LIST WHERE TEMP_VALUES_LIST.FIELD_TYPE='" . $model_field_name . "-" . $model_class . "' AND " . $sql_field_name . "=TEMP_VALUES_LIST.FIELD_VALUE) ";
        }
        return $this;
    }

    public function appendParams(Array $params)
    {
        $this->_params = array_merge($this->_params, $params);
        return $this;
    }

    public function groupBy($sql_text)
    {
        $this->_group_by = ' ' . $sql_text;
        return $this;
    }

    public function orderBy($sql_text)
    {
        $this->_order_by = ' ' . $sql_text;
        return $this;
    }

    public function cache($duration)
    {
        $this->_cache_duration = $duration;
        return $this;
    }

    public function getSQL()
    {
        return $this->_sql;
    }

    public function setSQL($sql)
    {
        $this->_sql = $sql;
        return $this;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function setParams(Array $params)
    {
        $this->_params = $params;
        return $this;
    }

    public function makeCountQuery()
    {
        $old_sql = $this->_sql;
        $this->_sql = preg_replace("/^select\s([^\>(]+)\sfrom\s/i", "SELECT COUNT(*) FROM ", $this->_sql);
        $count = $this->queryScalar();
        $this->_sql = $old_sql;
        return $count;
    }

    public function queryScalar()
    {
        $this->buildSQL(false);
        return $this->buildCommand()->queryScalar();
    }

    public function buildSQL($order_by = true)
    {
        if (Helper::isOk($this->_group_by)) {
            $this->_sql .= ' ' . $this->_group_by;
        }
        if ($order_by && Helper::isOk($this->_order_by)) {
            $this->_sql .= ' ' . $this->_order_by;
        }
    }

    private function buildCommand()
    {
        $db = $this->_db;
        $command = Yii::$app->$db->createCommand($this->_sql)->bindValues($this->_params);
        if (Helper::isOk($this->_cache_duration)) {
            $command->cache($this->_cache_duration);
        }
        return $command;
    }

    public function queryAll()
    {
        $this->buildSQL();
        return $this->buildCommand()->queryAll();
    }

    public function queryOne()
    {
        $this->buildSQL();
        return $this->buildCommand()->queryOne();
    }

    public function execute()
    {
        $this->buildSQL(false);
        return $this->buildCommand()->execute();
    }

}
