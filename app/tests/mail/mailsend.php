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
$aData = array(
    'email' => 'jingxin.xu@51fanli.com',
    'template' => 'happybirthday',
    'servicetype' => 'webpower'
);
sendMail($aData, $oRedis);
