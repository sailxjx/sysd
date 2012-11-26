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
                Const_MailTemp::F_UTIME
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

    protected function setMailTemp($aParams = array()) {
        if(empty($aParams[Const_MailTemp::F_NAME])){
            return $this->errReply(null, 'mail template name is not defined!');
        }
        $oSMailTemp = Store_MailTemp::getIns();
        if($aParams['action'] == 'del'){
            if($oSMailTemp->del($aParams[Const_MailTemp::F_NAME])){
                return $this->succReply(null, 'delete template success');
            }else{
                return $this->errReply(null, 'delete template error');
            }
        }else{
            unset($aParams['action']);
        }
        $aData = $aParams;
        $aData[Const_MailTemp::F_INUSE] = isset($aData[Const_MailTemp::F_INUSE])?$aData[Const_MailTemp::F_INUSE]:1;
        $aData[Const_MailTemp::F_UTIME] = time();
        if($oSMailTemp->set($aData)){
            return $this->succReply(null, 'mail template saved');
        }else{
            return $this->errReply(null, 'mail template edit failed');
        }
    }

    protected function setMailService($aParams) {
        if(empty($aParams[Const_Mail::C_SERVICE_NAME])|| empty($aParams[Const_Mail::C_SERVICE_TEMP])) {
            return $this->errReply(null, 'missing mail service params');
        }
        $oRedis = $this->oRedis;
        $sServiceKey = Redis_Key::mailServices();
        if(isset($aParams['action']) && $aParams['action'] == 'del'){
            $oRedis->hdel($sServiceKey, $aParams[Const_Mail::C_SERVICE_NAME]);
            return $this->succReply(null, 'mail service delete succ');
        }
        $aServiceFields = Const_Mail::getServiceFields();
        $aData = array_intersect_key($aParams, array_combine($aServiceFields, $aServiceFields));
        if(empty($aData)){
            return $this->errReply(null, 'missing mail service data');
        }
        if($aData[Const_Mail::C_SERVICE_SCORE] >= 0 ){
            $aData[Const_Mail::C_SERVICE_ERRTIMES] = 0;
        }
        $oRedis->hset($sServiceKey, $aData[Const_Mail::C_SERVICE_NAME], json_encode($aData));
        return $this->succReply(null, 'mail service edit succ');
    }
    
}
