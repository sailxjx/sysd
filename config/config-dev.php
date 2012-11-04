<?php
$config = require 'share.php';

// database configs
$config['MYSQL'] = array(
    'dsn' => 'mysql:host=192.168.100.60;dbname=51fanli_user',
    'user' => 'root',
    'pwd' => 'root',
    'options' => array() ,
    'statments' => array(
        'SET CHARACTER SET utf8'
    )
);

$config['REDIS'] = array(
    'host' => '127.0.0.1',
    'port' => 6379
);

$config['SERVER_ADDR'] = array(
    'master' => '127.0.0.1',
    'slave' => '127.0.0.1'
);

// init job list
$config['INIT_JOBS'] = array(
    'Server -d --log-file=' . APP_PATH . 'var/log/server.log'
);

// sub job lists started by server
$config['JOBS'] = array(
    'MailServer -d --log-file=' . APP_PATH . 'var/log/mailserver.log',
    'MailVent -d --log-file=' . APP_PATH . 'var/log/mailvent.log',
    'MailSink -d --log-file=' . APP_PATH . 'var/log/mailsink.log',
    'MailRedel -d --log-file=' . APP_PATH . 'var/log/mailredel.log',
    // 'MailWorker -d --log-file=' . APP_PATH . 'var/log/mailworker.log --daemon-num=3'
);
// return configs
return $config;
