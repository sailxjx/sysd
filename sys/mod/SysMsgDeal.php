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
            return $this->succReply(call_user_func(array(
                $this,
                $sFunc
            ) , $aParams));
        }
        return $this->errReply('error','could not find the called function');
    }

    protected function succReply($mData, $sMsg = 'succ'){
        $aData['status'] = 1;
        $aData['msg'] = $sMsg;
        $aData['data'] = $mData;
        return json_encode($aData);
    }

    protected function errReply($mData, $sMsg = 'error'){
        $aData['status'] = 0;
        $aData['msg'] = $sMsg;
        $aData['data'] = $mData;
        return json_encode($aData);
    }
    
    protected function getJobList() {
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
    
    protected function getJobSum() {
        return $this->oRedis->zcard(Redis_SysKey::sysProcRun());
    }    
}
