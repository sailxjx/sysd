<?php
$config = require_once 'share.php';

// database configs
$config['MSSQL'] = array(
    'dsn' => 'odbc:webdsn',
    'user' => 'trace',
    'pwd' => 'trace',
    'options' => array()
);

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
    'port' => 6380
);

// job lists
$config['CMD'] = array(
    'HelloWorld -d --daemon-num=3 --min-daemon-num=2 -w -q'
);

// return configs
return $config;