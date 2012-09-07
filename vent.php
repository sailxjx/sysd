<?php 
$context = new ZMQContext();

$oZock = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
$oZock->bind("ipc:///tmp/ztest.ipc");

for ($i = 0; $i < 10; $i++) {
    $oZock->send($i);
}
return true;
