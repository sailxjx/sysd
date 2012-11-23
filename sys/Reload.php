<?php
/**
 * Document: Reload
 * Reload all jobs or Reload by name
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Reload extends Base {
    
    protected function main() {
        $sJClass = $this->oCore->getJobClass();
        if (empty($sJClass)) {
            $this->reloadAll();
        } else {
            $this->reloadOne($sJClass);
        }
        return true;
    }
    
    protected function reloadAll() {
        $aJList = array_merge(Util::getConfig('INIT_JOBS') , Util::getConfig('JOBS'));
        $aCList = array(); //Class list
        foreach ((array)$aJList as $sOCmd) {
            $aOCmd = explode(' ', trim($sOCmd));
            $aCList[] = !empty($aOCmd[0]) ? $aOCmd[0] : null;
        }
        $aCList = array_filter(array_unique($aCList));
        foreach ((array)$aCList as $sJClass) {
            $this->reloadOne($sJClass);
        }
        return true;
    }
    
    protected function reloadOne($sJClass) {
        if (!reqClass($sJClass)) {
            Util::output('Class not exsit!');
            $this->oCore->showHelp();
            return false;
        }
        $aPid = Util_SysUtil::getProcIdsByClass($sJClass);
        $this->reloadProcByIds($aPid);
        return true;
    }
    
    protected function reloadProcByIds($aPids) {
        $iMyPid = posix_getpid();
        foreach ($aPids as $iPid) {
            if ($iMyPid == $iPid) { //if this function is called by a restart command, it will not be killed
                continue;
            }
            Util_SysUtil::reloadProcById($iPid);
        }
        return true;
    }
    
}
