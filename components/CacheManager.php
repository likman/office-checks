<?php
namespace app\components;

use app\models\User;
use Yii;

class CacheManager
{
    const MAX_CACHE_DURATION=3600;
    const DEFAULT_CACHE_DURATION=600;

    public static function getCacheData($key)
    {
        $user=User::getCurrentUser();
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn.' '.$user->id_human.' '.Yii::$app->session->getId();
        return Yii::$app->cache->get($cache_key);
    }

    public static function setCacheData($key, $value, $duration=360)
    {
        $user=User::getCurrentUser();
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn.' '.$user->id_human.' '.Yii::$app->session->getId();
        return Yii::$app->cache->set($cache_key, $value, $duration);
    }

    public static function deleteCacheData($key)
    {
        $user=User::getCurrentUser();
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn.' '.$user->id_human.' '.Yii::$app->session->getId();
        return Yii::$app->cache->delete($cache_key);
    }

    public static function getAppCacheData($key)
    {
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn;
        return Yii::$app->cache->get($cache_key);
    }

    public static function setAppCacheData($key, $value, $duration=360)
    {
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn;
        return Yii::$app->cache->set($cache_key, $value, $duration);
    }

    public static function deleteAppCacheData($key)
    {
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn;
        return Yii::$app->cache->delete($cache_key);
    }

    public static function getSessionData($key)
    {
        return Yii::$app->session->get($key);
    }

    public static function setSessionData($key, $value)
    {
        Yii::$app->session->set($key, $value);
    }

    public static function deleteSessionData($key, $value)
    {
        Yii::$app->session->remove($key);
    }

    public static function getFileCacheData($key)
    {
        $user=User::getCurrentUser();
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn.' '.$user->id_human.' '.Yii::$app->session->getId();
        return Yii::$app->cache->get($cache_key);
    }

    public static function setFileCacheData($key, $value, $time=null)
    {
        $user=User::getCurrentUser();
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn.' '.$user->id_human.' '.Yii::$app->session->getId();
        return Yii::$app->file_cache->set($cache_key, $value, $time);
    }

    public static function deleteFileCacheData($key)
    {
        $user=User::getCurrentUser();
        $cache_key=$key.' '.Yii::$app->id.' '.Yii::$app->db->dsn.' '.$user->id_human.' '.Yii::$app->session->getId();
        return Yii::$app->file_cache->delete($cache_key);
    }
}
