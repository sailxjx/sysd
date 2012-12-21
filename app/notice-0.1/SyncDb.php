<?php

class SyncDb extends Base {
    protected $aMailQueue = array(
        'mailFail',
        'mailSucc'
    );
    
    protected $aSmsQueue = array(
        'smsFail',
        'smsSucc'
    );
    
    protected $aLogQueue = array(
        'normal',
        'warning',
        'error'
    );
    
    protected $iStep = 100;
    
    protected function main() {
        $this->syncMail();
        $this->syncSms();
        $this->syncLog();
    }
    
    protected function syncMail() {
        $this->syncGo($this->aMailQueue, Store_Mail::getIns());
    }
    
    protected function syncSms() {
        $this->syncGo($this->aSmsQueue, Store_Sms::getIns());
    }
    
    protected function syncLog() {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        $oMLog = Mod_Log::getIns();
        $oPdo = Fac_SysDb::getIns()->loadPdo();
        $oRef = new ReflectionClass("Const_Log");
        $aCons = $oRef->getConstants();
        foreach ($this->aLogQueue as $sQueue) {
            Util::output('sync start: log '. $sQueue, 'notice');
            $sLevelKey = "LEVEL_".strtoupper($sQueue);
            $iLevel = isset($aCons[$sLevelKey])?$aCons[$sLevelKey]:null;
            if (!isset($iLevel)) {
                continue;
            }
            while ($sLog = $oMLog->popLog($sQueue)) {
                $sSql = 'INSERT INTO notice_log (`log`, `level`) VALUES (?, ?)';
                $aData = array($sLog, $iLevel);
                $oStmt = $oPdo->prepare($sSql);
                $oStmt->execute($aData);
                $iLastId= $oPdo->lastInsertId();
                Util::output('insert log: id: '. $iLastId. '; msg: '. $sLog, 'verbose');
            }
            Util::output('sync finish: log '. $sQueue, 'notice');
        }
    }
    
    protected function syncGo($aQueues, $oStore) {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        foreach ($aQueues as $sQKey) {
            $sRKey = Redis_Key::$sQKey();
            $sKStart = $sQKey . 'RangeStart';
            $sStartKey = Redis_Key::$sKStart();
            Util::output('sync start: ' . $sQKey, 'notice');
            while (1) {
                $iStart = $oRedis->get($sStartKey);
                $iStart = empty($iStart) ? 0 : $iStart;
                $iStop = $iStart + $this->iStep - 1;
                $aIds = $oRedis->zrevrange($sRKey, $iStart, $iStop);
                $iCnt = count($aIds);
                foreach ($aIds as $iId) {
                    $iRow = $oStore->syncDb($iId);
                    if ($iRow) {
                        Util::output('sync succ: ' . $sQKey . ': ' . $iId, 'notice');
                    } else {
                        Util::output('sync error: ' . $sQKey . ': ' . $iId, 'notice');
                    }
                }
                $iNewStart = $iStart + $iCnt;
                Util::output('sync range: ' . $sQKey . ': ' . $iStart . ',' . ($iNewStart) , 'notice');
                $oRedis->set($sStartKey, $iNewStart);
                if ($iCnt < $this->iStep) {
                    break;
                }
            }
            Util::output('sync finish: ' . $sQKey, 'notice');
        }
    }
}
