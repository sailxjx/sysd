<?php
class Mod_Log extends Mod_SysBase {
    protected $aQueues = array(
        'warning',
        'error',
        'normal'
    );
    protected $oRedis;
    protected $sDFormat = '[%t]: %m; %d; %c;';
    
    protected function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    public function __call($sFunc, $aArgs) {
        if (in_array($sFunc, $this->aQueues)) {
            return $this->log($sFunc, $aArgs);
        }
        return $this;
    }
    
    protected function log($sQueue, $aArgs) {
        $sFormat = is_string($aArgs[0])?$aArgs[0]:$this->sDFormat;
        if(!preg_match_all('/%[a-z]/', $sFormat, $aMatches)){
            return false;
        }
        unset($aArgs[0]);
        $aArgs = array_values($aArgs);
        $aData = array();
        foreach ($aMatches[0] as $k=>$v) {
            $aData[$v]=isset($aArgs[$k])?$aArgs[$k]:'';
        };
        $sKey = $this->getRedisKey($sQueue);
        return $this->oRedis->lpush($sKey, str_replace(array_keys($aData), array_values($aData), $sFormat));
    }
    
    protected function getRedisKey($sQueue) {
        $sKey = "log" . ucfirst($sQueue);
        return Redis_Key::$sKey();
    }
    
}
