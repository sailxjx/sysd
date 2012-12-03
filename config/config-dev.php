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

// redis server configs
$config['REDIS'] = array(
    'host' => '127.0.0.1',
    'port' => 6379,
    'expire' => 864000 // default expire
);

// zmq server configs; not in use now
$config['SERVER_ADDR'] = array(
    'master' => '127.0.0.1',
    'slave' => '127.0.0.1'
);

// debug (a lot of information, useful for development/testing)
// verbose (many rarely useful info, but not a mess like the debug level)
// notice (moderately verbose, what you want in production probably)
// warning (only very important / critical messages are logged)

$config['LOG_LEVEL'] = 'debug';

// init job list
$config['INIT_JOBS'] = array(
    'Server -d --log-file=' . APP_PATH . 'var/log/server.log'
);

// sub job lists started by server
$config['JOBS'] = array(
    // 'MailDataInit -d --log-file=' . APP_PATH . 'var/log/maildatainit.log', //need not listen, only once
    'MailServer -d --log-file=' . APP_PATH . 'var/log/mailserver.log -w',
    'MailVent -d --log-file=' . APP_PATH . 'var/log/mailvent.log -w',
    'MailSink -d --log-file=' . APP_PATH . 'var/log/mailsink.log -w',
    'MailRedel -d --log-file=' . APP_PATH . 'var/log/mailredel.log -w',
    'MailWorker -d --log-file=' . APP_PATH . 'var/log/mailworker.log --daemon-num=3 -w'
);
// return configs
return $config;
