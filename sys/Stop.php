<?php
/**
 * Document: Stop
 * Stop all jobs or stop by name
 * Created on: 2012-4-16, 16:53:25
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Stop extends Base {
    
    protected function main() {
        $sJClass = $this->oCore->getJobClass();
        if (empty($sJClass)) {
            $this->stopAll();
        } else {
            $this->stopOne($sJClass);
        }
        return true;
    }
    
    protected function stopAll() {
        $aPids = Util_SysUtil::getAllProcIds();
        $this->stopProcByIds($aPids);
        return true;
    }
    
    protected function stopOne($sJClass) {
        if (!reqClass($sJClass)) {
            Util::output('Class not exsit!');
            $this->oCore->showHelp();
            return false;
        }
        $aPids = Util_SysUtil::getProcIdsByClass($sJClass);
        $this->stopProcByIds($aPids);
        return true;
    }
    
    protected function stopProcByIds($aPids) {
        $iMyPid = posix_getpid();
        foreach ($aPids as $iPid) {
            if ($iMyPid == $iPid) { //if this function is called by a restart command, it will not be killed
                continue;
            }
            Util_SysUtil::stopProcById($iPid);
        }
        return true;
    }
    
}
