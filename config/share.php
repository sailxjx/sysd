<?php
// common configs
$config['DEBUG'] = 1;
$config['LOG_FILE'] = APP_PATH . 'var/log/sys.log';
$config['PID_PATH'] = APP_PATH . 'var/tmp/';
$config['MAN_PAGE'] = APP_PATH . 'var/man/man.json';
$config['HOOK_PATH'] = APP_PATH . 'var/hooks/';
$config['MAX_DAEMON_NUM'] = 10;

// bind modules
$config['MOD_TASK'] = 'RTask'; //dicide which task distribute module to use
$config['MOD_STATUS'] = 'ZStatus'; //module to scan the health of process

return $config;