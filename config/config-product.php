<?php
$config = require 'share.php';

// database configs
$config['REDIS'] = array(
    'host' => 'zhihui.redis1.51fanli.it',
    'port' => 6380
);

// init job list
$config['INIT_JOBS'] = array(
    'Server -d --log-file=' . APP_PATH . 'var/log/server.log'
);

// sub job lists started by server
$config['JOBS'] = array(
    'MailServer -d --log-file=' . APP_PATH . 'var/log/mailserver.log -w',
    'MailVent -d --log-file=' . APP_PATH . 'var/log/mailvent.log -w',
    'MailSink -d --log-file=' . APP_PATH . 'var/log/mailsink.log -w',
    'MailRedel -d --log-file=' . APP_PATH . 'var/log/mailredel.log -w',
    // 'MailWorker -d --log-file=' . APP_PATH . 'var/log/mailworker.log --daemon-num=3'
);

// return configs
return $config;
