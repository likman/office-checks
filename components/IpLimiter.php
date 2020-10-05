<?php
namespace app\components;

use yii;
use yii\filters\RateLimitInterface;

class IpLimiter implements RateLimitInterface
{
    const MAX_LOGIN_ATTEMPTS = 10;
    const LOGIN_ATTEMPTS_TIME = 10 * 60; //seconds
    const LOGIN_BAN_TIME = 15 * 60; //seconds

    private $allowed_requests_guest = 12;
    private $allowed_requests_user = 20;
    private $window = 10;
    private $max_violation_count = 10;

    public $allowance;

    public function getRateLimit($request, $action)
    {
        if (Yii::$app->user->isGuest) {
            return [$this->allowed_requests_guest, $this->window];
        } else {
            return [$this->allowed_requests_user, $this->window];
        }
    }

    public function loadAllowance($request, $action)
    {
        $ip = self::getRealIp();
        if (self::isIpBlockedRateLimit($ip)) {
            die('Приходите позднее...');
        }
        $cache = Yii::$app->getCache();
        $key = $ip;
        return [
            $cache->get(Yii::$app->id . '.ratelimit.ip.allowance.' . $key),
            $cache->get(Yii::$app->id . '.ratelimit.ip.allowance_updated_at.' . $key),
        ];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $ip = self::getRealIp();
        $cache = Yii::$app->getCache();
        $cache->set(Yii::$app->id . '.ratelimit.ip.allowance.' . $ip, $allowance);
        $cache->set(Yii::$app->id . '.ratelimit.ip.allowance_updated_at.' . $ip, $timestamp);
        if ($allowance == 0) {
            $cacheViolationKey = Yii::$app->id . '.ratelimit.ip.attempts' . $ip;
            $attempts = $cache->get($cacheViolationKey);
            if ($attempts === false) {
                $attempts = 0;
            }
            if ($attempts > $this->max_violation_count) {
                self::blockRateLimitByIp($ip);
            } else {
                $attempts++;
                $cache->set($cacheViolationKey, $attempts, 360);
            }
        }
    }

    public static function getRealIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (Helper::isOk($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }

    /**
     * Returns true if IP has reached max attempts and banned
     * @return bool
     */
    public static function incrementLoginAttempts()
    {
        $ip = IpLimiter::getRealIp();
        $key = Yii::$app->id . '.login.ip.attempts.' . $ip;
        $attempts = CacheManager::getAppCacheData($key);
        if ($attempts === false) {
            $attempts = 0;
        }
        $attempts++;
        CacheManager::setAppCacheData($key, $attempts, self::LOGIN_ATTEMPTS_TIME);
        if ($attempts > self::MAX_LOGIN_ATTEMPTS) {
            self::banIpForLogin($ip);
            return true;
        }
        return false;
    }

    public static function blockRateLimitByIp($ip, $duration = 360)
    {
        CacheManager::setAppCacheData('.ratelimit.ip.block.' . $ip, 1, $duration);
    }

    public static function banIpForLogin($ip, $duration = self::LOGIN_BAN_TIME)
    {
        CacheManager::setAppCacheData('login.ip.ban.' . $ip, 1, $duration);
    }

    public static function isIpBannedForLogin($ip)
    {
        $isBlocked = CacheManager::getAppCacheData('login.ip.ban.' . $ip);
        if (Helper::isOk($isBlocked) && $isBlocked == 1) {
            return true;
        }
        return false;
    }

    public static function isIpBlockedRateLimit($ip)
    {
        $isBlocked = CacheManager::getAppCacheData('.ratelimit.ip.block.' . $ip);
        if (Helper::isOk($isBlocked) && $isBlocked == 1) {
            return true;
        }
        return false;
    }
}
