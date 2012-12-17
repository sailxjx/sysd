<?php
class Mod_MsgDeal extends Mod_SysMsgDeal {
    
    protected function getMailSum() {
        $aQueues = Queue_Mail::getIns()->getQueues();
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
        $aMailIds = $oRedis->zrange(Redis_Key::mailTemplates() , 0, -1);
        $oSMailTemp = Store_MailTemp::getIns();
        $aMailTemps = array();
        foreach ($aMailIds as $iMailId) {
            $aMailTemps[] = $oSMailTemp->get($iMailId, array(
                Const_MailTemp::F_NAME,
                Const_MailTemp::F_WEBPOWERID,
                Const_MailTemp::F_INUSE,
                Const_MailTemp::F_DESC,
            ));
        }
        return $this->succReply($aMailTemps);
    }
    
    protected function getMailTemp($aParams = array()) {
        if (empty($aParams[Const_MailTemp::F_NAME])) {
            return $this->errReply(null, 'mail template name is not defined!');
        }
        $aMailTemp = Store_MailTemp::getIns()->get($aParams[Const_MailTemp::F_NAME]);
        if (empty($aMailTemp)) {
            return $this->errReply(null, 'mail template not found!');
        } else {
            return $this->succReply($aMailTemp);
        }
    }
    
    protected function setMailTemp($aParams = array()) {
        if (empty($aParams[Const_MailTemp::F_NAME])) {
            return $this->errReply(null, 'mail template name is not defined!');
        }
        $oSMailTemp = Store_MailTemp::getIns();
        if ($aParams['action'] == 'del') {
            if ($oSMailTemp->del($aParams[Const_MailTemp::F_NAME])) {
                return $this->succReply(null, 'delete template success');
            } else {
                return $this->errReply(null, 'delete template error');
            }
        } else {
            unset($aParams['action']);
        }
        $aData = $aParams;
        $aData[Const_MailTemp::F_INUSE] = isset($aData[Const_MailTemp::F_INUSE]) ? $aData[Const_MailTemp::F_INUSE] : 1;
        $aData[Const_MailTemp::F_UTIME] = time();
        if ($oSMailTemp->set($aData)) {
            return $this->succReply(null, 'mail template saved');
        } else {
            return $this->errReply(null, 'mail template edit failed');
        }
    }
    
    protected function setMailService($aParams) {
        if (empty($aParams[Const_Mail::C_SERVICE_NAME]) || empty($aParams[Const_Mail::C_SERVICE_TEMP])) {
            return $this->errReply(null, 'missing mail service params');
        }
        $oRedis = $this->oRedis;
        $sServiceKey = Redis_Key::mailServices();
        if (isset($aParams['action']) && $aParams['action'] == 'del') {
            $oRedis->hdel($sServiceKey, $aParams[Const_Mail::C_SERVICE_NAME]);
            return $this->succReply(null, 'mail service delete succ');
        }
        $aServiceFields = Const_Mail::getServiceFields();
        $aData = array_intersect_key($aParams, array_combine($aServiceFields, $aServiceFields));
        if (empty($aData)) {
            return $this->errReply(null, 'missing mail service data');
        }
        $aData[Const_Mail::C_SERVICE_SCORE] = intval($aData[Const_Mail::C_SERVICE_SCORE]);
        if ($aData[Const_Mail::C_SERVICE_SCORE] >= 0) {
            $aData[Const_Mail::C_SERVICE_ERRTIMES] = 0;
        }
        $oRedis->hset($sServiceKey, $aData[Const_Mail::C_SERVICE_NAME], json_encode($aData));
        return $this->succReply(null, 'mail service edit succ');
    }
    
    protected function getHbConfigs() {
        $aHbBoxes = $this->oRedis->hgetall(Redis_Key::mailHbBoxes());
        if (empty($aHbBoxes)) {
            return $this->succReply($aHbBoxes);
        }
        foreach ($aHbBoxes as $sAddr => $sBox) {
            $aHbBoxes[$sAddr] = json_decode($sBox, true);
        }
        return $this->succReply($aHbBoxes);
    }
    
    protected function getSmsTempSum() {
        $oRedis = $this->oRedis;
        $aSmsIds = $oRedis->zrange(Redis_Key::smsTemplates() , 0, -1);
        $oSSmsTemp = Store_SmsTemp::getIns();
        $aSmsTemps = array();
        foreach ($aSmsIds as $iSmsId) {
            $aSmsTemps[] = $oSSmsTemp->get($iSmsId, array(
                Const_SmsTemp::F_NAME,
                Const_SmsTemp::F_INUSE,
                Const_SmsTemp::F_DESC
            ));
        }
        return $this->succReply($aSmsTemps);
    }
    
    protected function getSmsTemp($aParams) {
        if (empty($aParams[Const_SmsTemp::F_NAME])) {
            return $this->errReply(null, 'sms template name is not defined!');
        }
        $aSmsTemp = Store_SmsTemp::getIns()->get($aParams[Const_SmsTemp::F_NAME]);
        if (empty($aSmsTemp)) {
            return $this->errReply(null, 'sms template not found!');
        } else {
            return $this->succReply($aSmsTemp);
        }
    }
    
    protected function setSmsTemp($aParams = array()) {
        if (empty($aParams[Const_SmsTemp::F_NAME])) {
            return $this->errReply(null, 'sms template name is not defined!');
        }
        $oSSmsTemp = Store_SmsTemp::getIns();
        if ($aParams['action'] == 'del') {
            if ($oSSmsTemp->del($aParams[Const_SmsTemp::F_NAME])) {
                return $this->succReply(null, 'delete template success');
            } else {
                return $this->errReply(null, 'delete template error');
            }
        } else {
            unset($aParams['action']);
        }
        $aData = $aParams;
        $aData[Const_SmsTemp::F_INUSE] = isset($aData[Const_SmsTemp::F_INUSE]) ? $aData[Const_SmsTemp::F_INUSE] : 1;
        $aData[Const_SmsTemp::F_UTIME] = time();
        if ($oSSmsTemp->set($aData)) {
            return $this->succReply(null, 'sms template saved');
        } else {
            return $this->errReply(null, 'sms template edit failed');
        }
    }
    
    protected function getSmsSum() {
        $aQueues = Queue_Sms::getIns()->getQueues();
        $sPreQueue = Queue_Sms::getIns()->getQueue();
        $oRedis = $this->oRedis;
        $oRedis->multi();
        foreach ($aQueues as $sQueue => $aQueue) {
            $sKey = $sPreQueue . ucfirst($sQueue);
            $oRedis->zcard(Redis_Key::$sKey());
        }
        $aSmses = $oRedis->exec();
        if (empty($aSmses)) {
            return $this->errReply(null, 'sms not exist');
        } else {
            return $this->succReply(array_combine(array_keys($aQueues) , $aSmses));
        }
    }
    
    protected function getQueueSum() {
        $aMailSum = json_decode($this->getMailSum() , true);
        $aSmsSum = json_decode($this->getSmsSum() , true);
        $aData['queues'] = array_unique(array_keys($aMailSum['data']) + array_keys($aSmsSum['data']));
        $aData['mail'] = $aMailSum['data'];
        $aData['sms'] = $aSmsSum['data'];
        return $this->succReply($aData);
    }
    
    protected function getSmsChannels() {
        $oRedis = $this->oRedis;
        $aServices = $oRedis->hgetall(Redis_Key::smsServices());
        return $this->succReply($aServices);
    }
    
    protected function getSmsChannel($aParams) {
        $sName = isset($aParams[Const_Sms::C_SERVICE_NAME]) ? $aParams[Const_Sms::C_SERVICE_NAME] : null;
        if (empty($sName)) {
            return $this->errReply(null, 'sms channel name should not be null');
        }
        $sChannel = $this->oRedis->hget(Redis_Key::smsServices() , $sName);
        if (empty($sChannel)) {
            return $this->errReply(null, "channel {$sName} not found!");
        }
        $aChannel = json_decode($sChannel, true);
        if (empty($aChannel[Const_Sms::C_SERVICE_NAME])) {
            $aChannel[Const_Sms::C_SERVICE_NAME] = $sName;
        }
        return $this->succReply($aChannel);
    }
    
    protected function setSmsChannel($aParams) {
        if (empty($aParams[Const_Sms::C_SERVICE_NAME]) || empty($aParams[Const_Sms::C_SERVICE_URL])) {
            return $this->errReply(null, 'missing sms service params');
        }
        $oRedis = $this->oRedis;
        $sServiceKey = Redis_Key::smsServices();
        if (isset($aParams['action']) && $aParams['action'] == 'del') {
            $oRedis->hdel($sServiceKey, $aParams[Const_Sms::C_SERVICE_NAME]);
            return $this->succReply(null, 'sms service delete succ');
        }
        $aServiceFields = Const_Sms::getServiceFields();
        $aData = array_intersect_key($aParams, array_combine($aServiceFields, $aServiceFields));
        if (empty($aData)) {
            return $this->errReply(null, 'missing sms service data');
        }
        $aData[Const_Sms::C_SERVICE_SCORE] = intval($aData[Const_Sms::C_SERVICE_SCORE]);
        $oRedis->hset($sServiceKey, $aData[Const_Sms::C_SERVICE_NAME], json_encode($aData));
        return $this->succReply(null, 'sms service edit succ');
    }
    
}
