<?php
function sendSms($sMobile, $sTemplate, $aParams = array()) {
    $oRedis = new Redis();
    $oRedis->connect('127.0.0.1', 6379);
    $aSmsParams = array(
        'params' => $aParams
    );
    $aSms = array(
        'mobile' => $sMobile,
        'template' => $sTemplate,
        'type' => 1,
        'smsparams' => json_encode($aSmsParams),
        'servicetype' => 'zxt'
    );
    return $oRedis->lpush('notice:sms:server', json_encode($aSms));
}
sendSms('15021374552', 'smstest', array(
    'username' => 'admin'
));
