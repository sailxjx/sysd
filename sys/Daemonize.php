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
        for ($i = 0;$i < $iDNum;$i++) {
            $iPid = pcntl_fork();
            if ($iPid === - 1) {
                Util::output('could not fork');
            } elseif ($iPid) { //parent
                if ($i < ($iDNum - 1)) {
                    continue;
                }
                exit;
            } else { //child
                chdir('/tmp');
                umask(022);
                // detatch from the controlling terminal
                if (posix_setsid() == - 1) {
                    Util::output("could not detach from terminal");
                    exit;
                }
                break; //break the parent loop
            }
        }
        return true;
    }
    
}
