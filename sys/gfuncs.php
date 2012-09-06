<?php

spl_autoload_register('uAutoLoad');

function uAutoLoad($sName) {
    return reqClass($sName);
}

function reqClass($sClass) {
    if (class_exists($sClass)) {
        return true;
    }
    global $G_LOAD_PATH;
    $aPath = explode('_', $sClass);
    $iCnt = count($aPath) - 1;
    $sFix = '';
    for ($i = 0; $i < $iCnt; $i++) {
        $sFix.=strtolower($aPath[$i]) . '/';
    }
    foreach ($G_LOAD_PATH as $sDir) {
        $sFile = $sDir . $sFix . $aPath[$iCnt] . '.php';
        if (file_exists($sFile)) {
            require_once $sFile;
            return true;
        }
    }
    return false;
}

function getPackage() {
    static $aPack;
    if (!isset($aPack)) {
        $sFile = APP_PATH . "config/pack.json";
        if (is_file($sFile)) {
            $aPack = json_decode(file_get_contents($sFile), true);
        } else {
            trigger_error('could not find packages!', E_USER_ERROR);
        }
    }
    return $aPack;
}

function getLoadPath() {
    $aPack = getPackage();
    $aLoadPath = array();
    foreach ((array) $aPack['depends'] as $sDep => $sDVer) {
        $sDir = APP_PATH . 'app/' . $sDep . '/';
        if (!is_dir($sDir)) {
            trigger_error('could not find the depends dir! dirname: ' . $sDir, E_USER_WARNING);
        } else {
            $aLoadPath[] = APP_PATH . 'app/' . $sDep . '/';
        }
    }
    $aLoadPath[] = APP_PATH . 'sys/'; //load system path
    return $aLoadPath;
}

function uErrorHandler($etype, $msg, $file, $line) {
    if ($etype === E_USER_ERROR) {
        debug_backtrace();
    }
    return true;
}

