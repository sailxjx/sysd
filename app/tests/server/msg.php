<?php
if (empty($argv[1])) {
    echo 'please give me the function name';
    exit(1);
}
$aData = array(
    'func' => $argv[1],
    'params' => array()
);
$oZmq = new ZMQSocket(new ZMQContext() , ZMQ::SOCKET_REQ);
$oZmq->connect('tcp://127.0.0.1:5555');
$sMsg = $oZmq->send(json_encode($aData))->recv();
print_r(json_decode($sMsg, true));
