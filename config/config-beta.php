<?php
$config = require 'share.php';

// database configs
$config['REDIS'] = array(
    'host' => '192.168.2.222',
    'port' => 6380
);

// job lists
$config['CMD'] = array(
    'MailServer -d --log-file=' . APP_PATH . 'var/log/mailserver.log',
    'MailVent -d --log-file=' . APP_PATH . 'var/log/mailvent.log',
    'MailSink -d --log-file=' . APP_PATH . 'var/log/mailsink.log',
    'MailRedel -d --log-file=' . APP_PATH . 'var/log/mailredel.log',
);

// return configs
return $config;
