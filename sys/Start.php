<?php
/**
 * Document: Start
 * Created on: 2012-4-16, 16:52:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Start extends Base {
    
    protected function main() {
        $sJClass = $this->oCore->getJobClass();
        if (empty($sJClass)) {
            $this->startAll();
        } else {
            $this->startOne($sJClass);
        }
        return true;
    }
    
    protected function startOne($sJClass) {
        if (!reqClass($sJClass)) {
            Util::output('Class not exsit!');
            $this->oCore->showHelp();
            return false;
        }
        $this->logStatus($sJClass);
        $sJClass::getIns()->run();
    }
    
    protected function startAll() {
        $aJList = Util::getConfig('CMD');
        $sCmd = '';
        foreach ($aJList as $sOriCmd) {
            $sCmd = APP_PATH . 'launcher.php start ' . $sOriCmd;
            Util_SysUtil::runCmd($sCmd);
        }
        return true;
    }
    
    protected function logStatus() {
        $aData = array(
            Const_SysProc::F_NAME => $this->oCore->getJobClass() ,
            Const_SysProc::F_START => time() ,
            Const_SysProc::F_PARAMS => json_encode($this->oCore->getParams()) ,
            Const_SysProc::F_OPTIONS => json_encode($this->oCore->getOptions()) ,
            Const_SysProc::F_PID => posix_getpid() ,
        );
        Store_SysProc::getIns()->set($aData);
        return true;
    }
    
}
