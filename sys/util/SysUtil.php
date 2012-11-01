<?php
/**
 * Document: SysUtil
 * Created on: 2012-5-17, 17:12:10
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Util_SysUtil {
    protected static $aDCmds = array(
        Const_SysCommon::C_START,
        Const_SysCommon::C_STOP,
        Const_SysCommon::C_RESTART,
        Const_SysCommon::C_RELOAD
    );
    
    public static function getPidFileByClass($sCName) {
        if (empty($sCName)) {
            return null;
        }
        return Util::getConfig('PID_PATH') . $sCName . '.pid';
    }
    
    public static function stopProcById($iPid) {
        if (empty($iPid)) {
            return false;
        }
        if (posix_kill(intval($iPid) , SIGTERM)) {
            Util::output('Stop Process Succ:' . $iPid);
            return true;
        } else {
            Util::output('Stop Process Error:' . $iPid);
            return false;
        }
    }
    
    public static function reloadProcById($iPid) {
        if (empty($iPid)) {
            return false;
        }
        if (posix_kill(intval($iPid) , SIGHUP)) {
            Util::output('Reload Process Succ:' . $iPid);
            return true;
        } else {
            Util::output('Reload Process Error:' . $iPid);
            return false;
        }
    }
    
    public static function getProcIdsByClass($sJClass) {
        $sPidFile = self::getPidFileByClass($sJClass);
        $aPids = array();
        if (is_file($sPidFile)) {
            $sPids = Util::getFileCon($sPidFile);
            $aPids = explode(',', $sPids);
        }
        return $aPids;
    }
    
    public static function hashArgv($argv, $aDCmds = null) {
        $aDCmds = !isset($aDCmds) ? self::$aDCmds : $aDCmds;
        $sClassName = null;
        $aParams = array();
        $aOptions = array();
        $sCmd = null;
        foreach ($argv as $sArgv) {
            if (preg_match('/^--.*?=/i', $sArgv)) { //参数
                $sArgv = str_replace('--', '', $sArgv);
                $sArgv = str_replace('-', '_', $sArgv);
                parse_str($sArgv, $aTmp);
                $aParams = array_merge($aParams, $aTmp);
            } elseif (preg_match('/^--?.*/i', $sArgv)) { //选项
                $aOptions[] = $sArgv;
            } else {
                if (in_array($sArgv, $aDCmds)) { //默认命令
                    $sCmd = $sArgv;
                    continue;
                }
                $sClassName = $sArgv;
            }
        }
        return array(
            $sClassName,
            $aParams,
            $aOptions,
            $sCmd
        );
    }
    
    /**
     * param key to argv key
     * @param string $sPKey
     * @return string
     */
    public static function convParamKeyToArgsKey($sPKey) {
        return '--' . str_replace('_', '-', $sPKey);
    }
    
    public static function runFile($sFile, $sMode = 'w') {
        if (empty($sFile) || !is_file($sFile)) {
            return false;
        }
        if (!is_executable($sFile)) {
            Util::output('StartError[file is not executable!]-> ' . $sFile);
            return false;
        }
        return self::runCmd($sFile, $sMode);
    }
    
    /**
     * @todo 使用Daemon
     * @param type $sCmd
     * @param type $sMode
     * @return boolean
     */
    public static function runCmd($sCmd, $sMode = 'w') {
        if (empty($sCmd)) {
            return false;
        }
        if ($rProc = popen($sCmd, $sMode)) {
            pclose($rProc);
            Util::output('Start-> ' . $sCmd);
            return true;
        } else {
            Util::output('StartError-> ' . $sCmd);
            return false;
        }
    }
    
    /**
     * @todo some better ideas?
     * @param int $iPid
     * @return string
     */
    public static function getSysProcStatusByPid($iPid) {
        return shell_exec("ps -p {$iPid}|grep {$iPid}");
    }
    
    /**
     * @todo some better ideas?
     * @param string $sClass
     * @return int
     */
    public static function getSysProcNumByClass($sClass) {
        return shell_exec("ps -ef|grep '{$sClass}'|grep -v grep|wc -l");
    }
    
}
