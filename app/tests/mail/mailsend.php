<?php
function sendMail($aData, $oRedis) {
    $aFields = array(
        'email' => 'email',
        'template' => 'template',
        'servicetype' => 'servicetype',
        'mailparams' => 'mailparams'
    );
    $aData = array_intersect_key($aData, $aFields);
    return $oRedis->lpush('notice:mail:server', json_encode($aData));
}

$oRedis = new Redis();
$oRedis->connect('127.0.0.1', 6379);
$aParams = array(
        'hbcode'=>'admin'
);
$aData = array(
    'email' => 'jingxin.xu@51fanli.com',
    'template' => 'heartbeat',
    'servicetype' => 'easeye',
    'mailparams'=>json_encode($aParams)
);
sendMail($aData, $oRedis);
