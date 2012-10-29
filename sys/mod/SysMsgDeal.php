<?php
class Mod_SysMsgDeal extends Mod_SysBase {
    
    protected $oRedis;
    
    protected function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    public function deal($sMsg) {
        $aMsg = json_decode($sMsg, true);
        $sFunc = $aMsg['func'];
        $aParams = $aMsg['params'];
        if (method_exists($this, $sFunc)) {
            return json_encode(call_user_func(array(
                $this,
                $sFunc
            ) , $aParams));
        }
        return false;
    }
    
    protected function getConfigs() {
        return Util::getConfig();
    }
    
    public function getJobs() {
        $aRunJobIds = Queue_SysProc::getIns()->range('run');
        if (empty($aRunJobIds)) {
            return array();
        } else {
            $this->oRedis->multi();
            foreach ($aRunJobIds as $iRunJobId) {
                $this->oRedis->hgetall(Redis_SysKey::sysProcTable(array(
                    'id' => $iRunJobId
                )));
            }
            return $this->oRedis->exec();
        }
    }
}
