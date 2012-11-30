<?php
/**
 * Document: Util
 * Created on: 2012-4-27, 10:34:10
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Util {
    
    protected static $aConfigs;
    protected static $aLogLevels = array(
        'debug',
        'verbose',
        'notice',
        'warning'
    );
    protected static $iLogLevel;
    
    /**
     * 读取配置文件
     * @param string $sKey
     * @return mix
     */
    public static function getConfig($sKey = null) {
        if (!isset(self::$aConfigs)) {
            $sEnv = ENV;
            $sFile = APP_PATH . "config/config-{$sEnv}.php";
            if (is_file($sFile)) {
                self::$aConfigs = include $sFile;
            } else {
                trigger_error('could not find config file!', E_USER_ERROR);
            }
        }
        if (!empty($sKey)) {
            return isset(self::$aConfigs[$sKey]) ? self::$aConfigs[$sKey] : null;
        } else {
            return self::$aConfigs;
        }
        
    }
    
    public static function getLogLevel() {
        if (!isset(self::$iLogLevel)) {
            $sLogLevel = Util::getConfig('LOG_LEVEL');
            $sLogLevel = isset($sLogLevel) ? $sLogLevel : null;
            $iIdxKey = array_search($sLogLevel, self::$aLogLevels);
            if ($iIdxKey === false) {
                $iLogLevel = '1';
            } else {
                $iLogLevel = $iIdxKey;
            }
            self::$iLogLevel = $iLogLevel;
        }
        return self::$iLogLevel;
    }
    
    public static function reloadConfig() {
        self::$aConfigs = null;
        return true;
    }
    
    public static function xmlToArray($sXmlFile) {
        $oSXml = simplexml_load_file($sXmlFile);
        return json_decode(json_encode($oSXml) , true);
    }
    
    public static function objToArray($obj) {
        $arr = array();
        foreach ((array)$obj as $sKey => $mVal) {
            if (is_object($mVal)) {
                $arr[$sKey] = self::objToArray($mVal);
            } else {
                $arr[$sKey] = $mVal;
            }
        }
        return $arr;
    }
    
    public static function getFileCon($sFile, $sSetContent = '') {
        if (file_exists($sFile)) {
            return file_get_contents($sFile);
        } else {
            if (!empty($sSetContent)) {
                $sDir = dirname($sFile);
                if (!is_dir($sDir)) {
                    mkdir($sDir, 0777, true);
                }
                file_put_contents($sFile, $sSetContent);
            }
            return '';
        }
    }
    
    public static function setFileCon($sFile, $sContent, $iOption = FILE_BINARY) {
        if (!file_exists($sFile)) {
            $sDir = dirname($sFile);
            if (!is_dir($sDir)) {
                mkdir($sDir, 0777, true);
            }
        }
        return file_put_contents($sFile, $sContent, $iOption);
    }
    
    public static function output() {
        $aArgs = func_get_args();
        $sCon = '';
        $iLogLevel = self::getLogLevel();
        foreach ($aArgs as $mArg) {
            if (is_string($mArg)) {
                if (($iIdxLevel = array_search($mArg, self::$aLogLevels)) !== false) {
                    if ($iLogLevel > $iIdxLevel) {
                        return false;
                    } else {
                        continue;
                    }
                }
            }
            $sCon.= print_r($mArg, true);
        }
        echo self::formatLog($sCon);
        return true;
    }
    
    public static function logInfo($mCon, $sLogFile = null) {
        $sCon = self::formatLog($mCon);
        $sLogFile = empty($sLogFile) ? Core::getIns()->getLogFile() : $sLogFile;
        self::setFileCon($sLogFile, $sCon, FILE_APPEND);
        return true;
    }
    
    public static function formatLog($mCon) {
        return date('Y-m-d H:i:s') . '[memuse: ' . memory_get_usage(true) / 1024 . 'K]:[' . Core::getIns()->getJobClass() . '] ' . var_export($mCon, true) . PHP_EOL;
    }
    
    public static function report($iCode = 0, $sMsg = '') {
        //@todo error report
        
        
    }
    
    public static function getMyIp($sIfName = 'eth0') {
        $sIfConfig = shell_exec("ifconfig {$sIfName}");
        if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $sIfConfig, $aMatch)) {
            return $aMatch[1];
        }
        return false;
    }
    
}
