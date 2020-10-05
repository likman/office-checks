<?php
namespace app\components;

use Da\QrCode\QrCode;

class QrCodeHelper
{

    public static function getQrCode($text)
    {
        $qr = (new QrCode($text))
            ->setSize(150)
            ->setMargin(5);
        return $qr;
    }

}