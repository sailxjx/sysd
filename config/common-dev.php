<?php

// common configs
$config['LOG_FILE'] = APP_PATH . 'var/log/sys.log';
$config['PID_PATH'] = APP_PATH . 'var/tmp/';
$config['MAN_PAGE'] = APP_PATH . 'var/man/man.json';
$config['HOOK_PATH'] = APP_PATH . 'var/hooks/';
$config['MAX_DAEMON_NUM'] = 10;
$config['MSSQL'] = array(
    'dsn' => 'odbc:webdsn',
    'user' => 'trace',
    'pwd' => 'trace',
    'options' => array()
);

// database configs
$config['MYSQL'] = array(
    'dsn' => 'mysql:host=192.168.100.60;dbname=51fanli_user',
    'user' => 'root',
    'pwd' => 'root',
    'options' => array(),
    'statments' => array(
        'SET CHARACTER SET utf8'
    )
);

$config['REDIS'] = array(
    'host' => '127.0.0.1',
    'port' => 6379
);


// bind modules
$config['MOD_TASK'] = 'ZTask';
$config['MOD_LOG'] = 'RLog';

// job lists
$config['CMD'] = array(
    'HelloWorld -d --daemon-num=3 --min-daemon-num=2 -w -q'
);

// return configs
return $config;