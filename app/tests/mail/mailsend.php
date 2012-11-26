<?php
function sendMail($aData, $oRedis) {
    $aFields = array(
        'email' => 'email',
        'title' => 'title',
        'sender' => 'sender',
        'receiver' => 'receiver',
        'mailparams' => 'mailparams',
        'template' => 'template'
    );
    $aData = array_intersect_key($aData, $aFields);
    if (count($aData) != count($aFields)) {
        return false;
    }
    return $oRedis->lpush('notice:mail:server', json_encode($aData));
}

$oRedis = new Redis();
$oRedis->connect('127.0.0.1', 6379);
$aPost = array(
    'email' => 'lei.chen@51fanli.com',
    'sender' => 'bash',
    'receiver' => 'liushuang',
    'title' => 'batch test',
    'username' => '::-)呵呵',
    'template' => 'getuserpwd',
    'age' => 1
);
$aParams = array(
    'params' => array(
        'username' => 'hellworld',
        'pwdurl' => 'http://www.baidu.com',
        'date' => date('Y-m-d H:i:s')
    )
);
$aData = array_merge($aPost, array(
    'mailparams' => json_encode($aParams)
));
// $aData['servicetype'] = 'webpower';
for ($i = 0;$i < 10;$i++) {
    $aData['sender'] = 'bash' . $i;
    sendMail($aData, $oRedis);
}
