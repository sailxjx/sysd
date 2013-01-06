<?php
function sendSms($sMobile, $sTemplate, $aParams = array()) {
    $oRedis = new Redis();
    $oRedis->connect('127.0.0.1', 6379);
    $aSmsParams = $aParams;
    $aSms = array(
        'mobile' => $sMobile,
        'template' => $sTemplate,
        'type' => 1,
        'smsparams' => json_encode($aSmsParams),
        'servicetype' => 'montnets'
    );
    return $oRedis->lpush('notice:sms:server', json_encode($aSms));
}
sendSms('18888888888', 'smstest', array(
    'username' => 'admin'
));
