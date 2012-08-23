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

function uErrorHandler($etype, $msg, $file, $line) {
	if ($etype === E_USER_ERROR) {
		debug_backtrace();
	}
	return true;
}