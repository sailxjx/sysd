<?php
class Mod_MsgDeal extends Mod_SysMsgDeal {
    
    protected function getMailSum() {
        $aQueues = Queue_Mail::getIns()->getMailQueues();
        $sPreQueue = Queue_Mail::getIns()->getQueue();
        $oRedis = $this->oRedis;
        $oRedis->multi();
        foreach ($aQueues as $sQueue => $aQueue) {
            $sKey = $sPreQueue . ucfirst($sQueue);
            $oRedis->zcard(Redis_Key::$sKey());
        }
        $aMail = $oRedis->exec();
        if(empty($aMail)){
            return $this->errReply(null, 'mail not exist');
        }else{
            return $this->succReply(array_combine(array_keys($aQueues), $aMail));
        }
    }

    protected function getMailChannels() {
        $oRedis = $this->oRedis;
        $aServices = $oRedis->hgetall(Redis_Key::mailServices());
        return $this->succReply($aServices);
    }
    
}
