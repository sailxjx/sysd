<?php
function sendMail($aData, $oRedis) {
    $aFields = array(
        'email' => 'email',
        'template' => 'template',
        'servicetype' => 'servicetype',
        'mailparams' => 'mailparams'
    );
    $aData = array_intersect_key($aData, $aFields);
    print_r($aData);
    exit;
    return $oRedis->lpush('notice:mail:server', json_encode($aData));
}

$oRedis = new Redis();
$oRedis->connect('192.168.2.71', 6380);
$aPost = array(
    'email' => 'jingxin.xu@51fanli.com',
    'template' => 'happybirthday',
    'servicetype' => 'webpower'
);
$aData = array_merge($aPost, array(
    'mailparams' => json_encode($aParams)
));
sendMail($aData, $oRedis);
