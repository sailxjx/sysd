<?php
// common configs
$config['DEBUG'] = 1;
$config['LOG_FILE'] = APP_PATH . 'var/log/sys.log';
$config['PID_PATH'] = APP_PATH . 'var/run/';
$config['MAN_PAGE'] = APP_PATH . 'var/man/man.json';
$config['HOOK_PATH'] = APP_PATH . 'var/hooks/';
$config['MAX_DAEMON_NUM'] = 10;

return $config;
