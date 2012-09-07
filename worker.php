<?php
$context = new ZMQContext();

$oZock = new ZMQSocket($context, ZMQ::SOCKET_PULL);
$oZock->connect("ipc:///tmp/ztest.ipc");

while(1){
    $str=$oZock->recv();
    echo $str,PHP_EOL;
}
