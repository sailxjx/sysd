<?php
/**
 * Document: Daemonize
 * Created on: 2012-4-27, 10:34:50
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Daemonize {
    
    private static $oIns;
    public static $aNoDaemonCmds = array(
        Const_SysCommon::C_RESTART,
        Const_SysCommon::C_STOP,
        Const_SysCommon::C_RELOAD
    );
    
    /**
     * instance of Daemonize
     * @return Daemonize
     */
    public static function &getIns() {
        if (!isset(self::$oIns)) {
            self::$oIns = new Daemonize();
        }
        return self::$oIns;
    }
    
    public function daemon() {
        $oCore = Core::getIns();
        if (in_array($oCore->getCmd() , self::$aNoDaemonCmds)) {
            return false;
        }
        $sPidFile = Util_SysUtil::getPidFileByClass($oCore->getJobClass());
        if (empty($sPidFile)) {
            Util::output('could not find pid file!');
            exit;
        }
        $iDNum = $oCore->getDaemonNum();
        $sPids = Util::getFileCon($sPidFile);
        $aPid = !empty($sPids) ? explode(',', $sPids) : array();
        for ($i = 0; $i < $iDNum; $i++) {
            $iPid = pcntl_fork();
            if ($iPid === - 1) {
                Util::output('could not fork');
            } elseif ($iPid) { //parent
                $aPid[] = $iPid;
                if ($i < ($iDNum - 1)) {
                    continue;
                } else {
                    Util::setFileCon($sPidFile, implode(',', $aPid));
                }
                exit;
            } else { //child
                register_shutdown_function('Daemonize::shutdown');
                chdir('/tmp');
                umask(022);
                // detatch from the controlling terminal
                if (posix_setsid() == - 1) {
                    Util::output("could not detach from terminal");
                    exit;
                }
                self::logStatus();
                self::ctrlSignal(); // if add control signals, some exceptions may be raised by zmq
                break; //break the parent loop
            }
        }
        return true;
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
    
    protected static function ctrlSignal() {
        declare(ticks = 1); //for signal control
        pcntl_signal(SIGTERM, "Daemonize::sigHandler");
        pcntl_signal(SIGINT, "Daemonize::sigHandler");
        pcntl_signal(SIGHUP, "Daemonize::sigHandler");
    }
    
    /**
     * 作业结束时删除正常结束的PID文件
     * @param array $aPidConf
     * @return boolean
     */
    public static function shutdown() {
        $iPid = posix_getpid();
        $sPidFile = Util_SysUtil::getPidFileByClass(Core::getIns()->getJobClass());
        if (!is_file($sPidFile)) {
            return false;
        }
        $sPids = file_get_contents($sPidFile);
        $aPids = explode(',', $sPids);
        $aPids = array_diff($aPids, array(
            $iPid
        ));
        if (empty($aPids)) {
            @unlink($sPidFile);
        } else {
            file_put_contents($sPidFile, implode(',', $aPids));
        }
        return true;
    }
    
    protected static function logStatus() {
        $oCore = Core::getIns();
        $aData = array(
            Const_SysProc::F_NAME => $oCore->getJobClass() ,
            Const_SysProc::F_START => time() ,
            Const_SysProc::F_PARAMS => json_encode($oCore->getParams()) ,
            Const_SysProc::F_OPTIONS => json_encode($oCore->getOptions()) ,
            Const_SysProc::F_PID => posix_getpid()
        );
        Store_SysProc::getIns()->set($aData);
        return true;
    }
    
}
