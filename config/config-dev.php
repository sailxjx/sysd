<?php
$config = require_once 'share.php';

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

// job lists
$config['CMD'] = array(
    'MailServer -d --log-file=' . APP_PATH . 'var/log/mailserver.log',
    'MailVent -d --log-file=' . APP_PATH . 'var/log/mailvent.log',
    'MailSink -d --log-file=' . APP_PATH . 'var/log/mailsink.log',
    'MailRedel -d --log-file=' . APP_PATH . 'var/log/mailredel.log',
    'MailWorker -d --log-file=' . APP_PATH . 'var/log/mailworker.log --daemon-num=3'
);

// return configs
return $config;
