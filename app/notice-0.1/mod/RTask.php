<?php
/**
 * Document: RTask
 * Created on: 2012-9-6, 16:43:00
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
class Mod_RTask extends Mod_Task {
    
    protected $oRedis;
    protected $aChannels = array(
        Const_Task::C_MAILSERVER => 'mailServer',
        Const_Task::C_MAILLIST => 'mailList',
        Const_Task::C_MAILRESULT => 'mailResult'
    );
    protected $iUSec = 10000; //usleep time
    
    protected function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function reset() {
        $this->aMsg = array();
        return $this;
    }
    
    public function recv() {
        $sMsgKey = $this->mChannel;
        while (!$sMsg = $this->oRedis->rpop(Redis_Key::$sMsgKey())) {
            usleep($this->iUSec);
        }
        return $sMsg;
    }
    
    public function send($sMsg = null) {
        if ($sMsg) {
            $this->msg($sMsg);
        }
        if (empty($this->aMsg)) {
            return false;
        }
        $this->oRedis->multi();
        $sMsgKey = $this->mChannel;
        foreach ($this->aMsg as $sMsg) {
            $this->oRedis->lpush(Redis_Key::$sMsgKey() , $sMsg);
        }
        $this->reset();
        return $this->oRedis->exec();
    }

}
