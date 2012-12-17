<?php
$oRedis = new Redis();
$oRedis->connect('127.0.0.1', 6379);

$aKeys = array(
    'notice:sms:table:*',
    'notice:sms:id',
    'notice:sms:server',
    'notice:sms:list:low',
    'notice:sms:list:high',
    'notice:sms:wait',
    'notice:sms:send',
    'notice:sms:error',
    'notice:sms:fail',
    'notice:sms:succ',
    'notice:sms:redel:times'
);
foreach ($aKeys as $k) {
    $ks = $oRedis->keys($k);
    if (empty($ks)) {
        continue;
    }
    $oRedis->multi();
    foreach ($ks as $kk) {
        $oRedis->del($kk);
    }
    $oRedis->exec();
}
