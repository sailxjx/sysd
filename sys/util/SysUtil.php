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
        $iPid = posix_getpid();
        return Util::getConfig('PID_PATH') . "{$sCName}_{$iPid}.pid";
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
        $sPidPath = Util::getConfig('PID_PATH') . "{$sJClass}_*.pid";
        $aPidFiles = glob($sPidPath);
        $aPids = array();
        foreach ($aPidFiles as $sPidFile) {
            $aPids[] = Util::getFileCon($sPidFile);
        }
        return $aPids;
    }
    
    public static function getAllProcIds() {
        $sPidPath = Util::getConfig('PID_PATH') . "*.pid";
        $aPidFiles = glob($sPidPath);
        $aPids = array();
        foreach ($aPidFiles as $sPidFile) {
            $aPids[] = Util::getFileCon($sPidFile);
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
        $sCmd = empty($sCmd) ? Const_SysCommon::C_START : $sCmd;
        return array(
            $sClassName,
            $aParams,
            $aOptions,
            $sCmd
        );
    }
    
    public static function getArgvFromStr($str) {
        $sDelimiter = ' ';
        $arr = explode($sDelimiter, $str);
        $aFinal = array();
        $bCFlag = false;
        foreach ($arr as $val) {
            if (strpos($val, '\'') === false && strpos($val, '"') === false && $bCFlag === false) {
                $aFinal[] = $val;
                continue;
            } elseif ($bCFlag === true) {
                $aFinal[count($aFinal) - 1].= $sDelimiter . str_replace(array(
                    '\'',
                    '"'
                ) , array(
                    '',
                    ''
                ) , $val);
                $bCFlag = false;
                continue;
            } else {
                $aFinal[] = str_replace(array(
                    '\'',
                    '"'
                ) , array(
                    '',
                    ''
                ) , $val);
                $bCFlag = true;
                continue;
            }
        }
        return $aFinal;
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
    
    public static function runJob($sCmd, $sClassName, $aOptions = array() , $aParams = array()) {
        $sRunCmd = self::getRunCmd($sCmd, $sClassName, $aOptions, $aParams);
        if (empty($sRunCmd)) {
            return false;
        }
        if (self::runCmd($sRunCmd)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getRunCmd($sCmd, $sClassName, $aOptions = array() , $aParams = array()) {
        if (empty($sClassName) || empty($sCmd)) {
            return false;
        }
        $sRunCmd = APP_PATH . "launcher {$sCmd} {$sClassName}";
        $aRunParams = array();
        foreach ($aParams as $k => $v) {
            $aRunParams[] = self::convParamKeyToArgsKey($k) . '=' . $v;
        }
        $sRunCmd.= ' ' . implode(' ', $aOptions) . ' ' . implode(' ', $aRunParams);
        return $sRunCmd;
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
    public static function getSysProcNumByCmd($sCmd) {
        return shell_exec("ps -ef|grep '{$sCmd}'|grep -v grep|wc -l");
    }
    
    /**
     * add pid in pid file
     * @return boolean
     */
    public static function addPid() {
        $sClassName = Core::getIns()->getJobClass();
        $iPid = posix_getpid();
        $sPidFile = self::getPidFileByClass($sClassName);
        return Util::setFileCon($sPidFile, $iPid);
    }
    
    /**
     * log runtime data in redis
     * @return array
     */
    public static function logRunData() {
        $oCore = Core::getIns();
        $aData = array(
            Const_SysProc::F_NAME => $oCore->getJobClass() ,
            Const_SysProc::F_START => time() ,
            Const_SysProc::F_PARAMS => json_encode($oCore->getParams()) ,
            Const_SysProc::F_OPTIONS => json_encode($oCore->getOptions()) ,
            Const_SysProc::F_PID => posix_getpid() ,
            Const_SysProc::F_PPID => posix_getppid()
        );
        $iRunId = Store_SysProc::getIns()->set($aData);
        $aData[Const_SysProc::F_ID] = $iRunId;
        Queue_SysProc::getIns()->run($iRunId, time())->add();
        $oCore->setRunData($aData);
        return $aData;
    }
    
    public static function sigHandler($iSignal) {
        Util::output("catch system signal![{$iSignal}]");
        switch ($iSignal) {
            case SIGTERM:
                exit;
            break;
            case SIGINT:
                exit;
            break;
            case SIGHUP:
                Util::reloadConfig();
            break;
            default:
            break;
        }
    }
    
    /**
     * 作业结束时删除正常结束的PID文件
     * @return boolean
     */
    public static function shutdown() {
        // self::clearRunData(); // clear run id in redis first
        return self::remPidInFile();
    }
    
    public static function remPidInFile($iPid = null, $sClassName = null) {
        $iPid = isset($iPid) ? $iPid : posix_getpid();
        $sClassName = isset($sClassName) ? $sClassName : Core::getIns()->getJobClass();
        $sPidFile = self::getPidFileByClass($sClassName);
        if (!is_file($sPidFile)) {
            return false;
        }
        return @unlink($sPidFile);
    }
    
    /**
     * delete run id in redis running queue
     * @return boolean
     */
    public static function clearRunData($iRunId = null) {
        if (!isset($iRunId)) {
            $aRunData = Core::getIns()->getRunData();
            $iRunId = !empty($aRunData[Const_SysProc::F_ID]) ? $aRunData[Const_SysProc::F_ID] : null;
            if (empty($iRunId)) {
                return false;
            }
        }
        Queue_SysProc::getIns()->run($iRunId)->rem();
        return true;
    }
    
}
