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
        Const_Task::C_MAILRESULT => 'mailResult',
        Const_Task::C_SMSSERVER => 'smsServer',
        Const_Task::C_SMSLIST_HIGH => 'smsListHigh',
        Const_Task::C_SMSLIST_LOW => 'smsListLow',
        Const_Task::C_SMSRESULT => 'smsResult'
    );
    protected $iUSec = 100000; //usleep time
    
    protected function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function reset() {
        $this->aMsg = array();
        return $this;
    }
    
    public function recv($bNoBlock = false) {
        $sMsgKey = $this->mChannel;
        while (!$sMsg = $this->oRedis->rpop(Redis_Key::$sMsgKey())) {
            if ($bNoBlock) {
                break;
            }
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
