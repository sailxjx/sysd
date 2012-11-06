<?php
/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends Base {
    
    protected $aJobList; //初始数据
    protected $iMaxRetry = 3; //重试次数
    protected $aErrTimes = array();
    protected $iSleep = 5; //睡眠间隔
    
    protected function main() {
        Util::output('Begin to Listen');
        $this->aJobList = $this->getJobList();
        while (1) {
            $this->listen();
            sleep($this->iSleep);
        }
        return true;
    }
    
    /**
     * read status date of last launcher
     * @todo how to handle these data?
     * @return array
     */
    protected function getInitData() {
        if (!isset($this->aJobList)) {
            $aInitData = Util::getFileCon(APP_PATH . self::$sInitFile);
            if (!empty($aInitData)) {
                $this->aJobList = json_decode(base64_decode($aInitData) , true);
            } else {
                $this->aJobList = array();
            }
        }
        return $this->aJobList;
    }
    
    protected function getJobList() {
        $aCmds = Util::getConfig('JOBS');
        $iMaxDNum = Util::getConfig('MAX_DAEMON_NUM');
        $aJobs = array();
        foreach ($aCmds as $sCmd) {
            list($sClass, $aParams, $aOptions, $sCmd) = $aJob = Util_SysUtil::hashArgv(Util_SysUtil::getArgvFromStr($sCmd));
            if (!array_intersect(array(
                Const_SysCommon::OL_LISTEN,
                Const_SysCommon::OS_LISTEN
            ) , $aOptions)) { // need not listening
                continue;
            }
            $aJobs[] = $aJob;
        }
        return $aJobs;
    }
    
    protected function listen() {
        $aJobList = $this->aJobList;
        foreach ($aJobList as $iIndex => $aJob) {
            list($sClass, $aParams, $aOptions, $sCmd) = $aJob;
            if (empty($sClass) || $this->getSetErrTimes($iIndex) >= $this->iMaxRetry) {
                continue;
            }
            $iNum = Util_SysUtil::getSysProcNumByClass($sClass);
            $iMaxNum = isset($aParams[Const_SysCommon::P_DAEMON_NUM]) ? intval($aParams[Const_SysCommon::P_DAEMON_NUM]) : 1;
            $iMinNum = isset($aParams[Const_SysCommon::P_MIN_DAEMON_NUM]) ? intval($aParams[Const_SysCommon::P_MIN_DAEMON_NUM]) : 1;
            if ($iNum >= $iMinNum) {
                continue;
            }
            $aParams[Const_SysCommon::P_DAEMON_NUM] = intval($iMaxNum - $iNum);
            if (Util_SysUtil::runJob($sCmd, $sClass, $aOptions, $aParams)) {
                Util::output('Revive Job Succ: ' . json_encode($aJob));
            } else {
                Util::output('Revive Job Error: ' . json_encode($aJob));
                $this->getSetErrTimes($iIndex, 1);
            }
        }
    }
    
    protected function getSetErrTimes($iIndex, $iIncr = 0) {
        $iErrTimes = isset($this->aErrTimes[$iIndex]) ? intval($this->aErrTimes[$iIndex]) : 0;
        $iErrTimes+= $iIncr;
        if ($iErrTimes > 0) {
            Util::output("Retry Times: {$iErrTimes}; job index: {$iIndex}");
        }
        $this->aErrTimes[$iIndex] = $iErrTimes;
        return $this->aErrTimes[$iIndex];
    }
    
}
