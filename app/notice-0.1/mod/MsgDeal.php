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
        if (empty($aMail)) {
            return $this->errReply(null, 'mail not exist');
        } else {
            return $this->succReply(array_combine(array_keys($aQueues) , $aMail));
        }
    }
    
    protected function getMailChannels() {
        $oRedis = $this->oRedis;
        $aServices = $oRedis->hgetall(Redis_Key::mailServices());
        return $this->succReply($aServices);
    }
    
    protected function getMailTempSum() {
        $oRedis = $this->oRedis;
        $aMailIds = $oRedis->zrange(Redis_Key::mailTemplates(), 0, -1);
        $oSMailTemp = Store_MailTemp::getIns();
        $aMailTemps = array();
        foreach ($aMailIds as $iMailId) {
            $aMailTemps[] = $oSMailTemp->get($iMailId, array(
                Const_MailTemp::F_NAME,
                Const_MailTemp::F_WEBPOWERID,
                Const_MailTemp::F_INUSE,
                Const_MailTemp::F_CTIME
            ));
        }
        return $this->succReply($aMailTemps);
    }

    protected function getMailTemp($aParams = array()) {
        if(empty($aParams[Const_MailTemp::F_NAME])) {
            return $this->errReply(null, 'mail template name is not defined!');
        }
        $aMailTemp = Store_MailTemp::getIns()->get($aParams[Const_MailTemp::F_NAME]);
        if(empty($aMailTemp)){
            return $this->errReply(null, 'mail template not found!');
        }else{
            return $this->succReply($aMailTemp);
        }
    }
    
}
