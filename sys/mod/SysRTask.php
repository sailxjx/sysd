<?php
/**
 * Document: RTask
 * Created on: 2012-9-6, 16:43:00
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
class Mod_RTask extends Mod_SysTask {
    
    protected $oRedis;
    protected $iUSec = 10000; //usleep time
    protected $aChannels = array(
        'sysProcStatus'
    );
    
    protected function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function reset() {
        $this->aMsg = array();
        return $this;
    }
    
    public function recv() {
        $sMsgKey = $this->aChannels[$this->mChannel];
        while (!$sMsg = $this->oRedis->rpop(Redis_Key::$sMsgKey())) {
            usleep($this->iUSec);
        }
        return $sMsg;
    }
    
    public function send() {
        if (empty($this->aMsg)) {
            return false;
        }
        $this->oRedis->multi();
        $sMsgKey = $this->aChannels[$this->mChannel];
        foreach ($this->aMsg as $sMsg) {
            $this->oRedis->lpush(Redis_Key::$sMsgKey() , $sMsg);
        }
        $this->reset();
        return $this->oRedis->exec();
    }
    
}
