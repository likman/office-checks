<?php

namespace app\components;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Yii;

class MikrotikRdpAllower
{

    public static function allowConnectionToPort($port)
    {
        $config = new Config([
            'host' => Yii::$app->params['mikrotikHost'],
            'user' => Yii::$app->params['mikrotikUser'],
            'pass' => Yii::$app->params['mikrotikPassword'],
            'port' => 8728,
        ]);
        $client = new Client($config);
        $query = new Query('/ip/firewall/nat/print');
        $query->where('chain', 'dstnat');
        $query->where('dst-port', $port);
        $query->where('to-ports', 3389);
        $response = $client->query($query)->read();
        if (!isset($response[0]['.id'])) {
            return false; //нет правила с нужным портом
        }
        foreach ($response as $resp) {
            $query = new Query('/ip/firewall/nat/set');
            $query->equal('.id', $resp['.id']);
            $query->equal('src-address', IpLimiter::getRealIp());
            $response = $client->query($query)->read();
            if (!is_array($response) || count($response) > 0) {
                return false;
            }
        }
        Logger::info('Пользователь ' . Yii::$app->user->id . ' дал доступ к RDP к порту ' . $port);
        return true;
    }

}