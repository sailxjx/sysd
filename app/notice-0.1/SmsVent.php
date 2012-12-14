<?php
class SmsVent extends Task_Base {
    protected $oRedis;
    protected $aPoolChannels = array(
        Const_Sms::C_POOL_HIGH => Const_Task::C_SMSLIST_HIGH,
        Const_Sms::C_POOL_LOW => Const_Task::C_SMSLIST_LOW
    );
    protected $aSvsPools;
    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->vent();
    }
    
    protected function vent() {
        $oTask = $this->oTask;
        $oQSms = Queue_Sms::getIns();
        while (1) {
            $aMsgs = $this->listen();
            $this->aSvsPools = $this->loadServicePools();
            foreach ($aMsgs as $iSmsId => $iScore) {
                $aSms = $this->decSms($iSmsId);
                if (empty($aSms[Const_Sms::F_SERVICETYPE])) {
                    $oQSms->move('wait', 'fail', $iSmsId, time());
                } else {
                    if ($oQSms->move('wait', 'send', $iSmsId, time())) {
                        $oTask->channel($this->aPoolChannels[$this->getPool($aSms)])->msg($iSmsId)->send();
                        Util::output('sending sms: ', $iSmsId, 'notice');
                    }
                }
            }
        }
    }
    
    protected function listen() {
        $oRedis = $this->oRedis;
        while (!$aMsgs = $oRedis->zrangebyscore(Redis_Key::smsWait() , '-inf', time() , array(
            'withscores' => true
        ))) {
            usleep($this->iInterval);
        }
        return $aMsgs;
    }
    
    protected function decSms($iSmsId) {
        $aSms = Store_Sms::getIns()->get($iSmsId);
        $aSms[Const_Sms::F_CONTENT] = $this->getSmsCon($aSms);
        $aSms[Const_Sms::F_SERVICETYPE] = $this->getServiceType($aSms);
        Store_Sms::getIns()->set($aSms);
        return $aSms;
    }
    
    protected function getServiceType(&$aSms) {
        $aSvsPools = $this->aSvsPools;
        $aTryServices = isset($aSms[Const_Mail::F_TRYSERVICE]) ? json_decode($aSms[Const_Mail::F_TRYSERVICE], true) : array();
        $sPool = $this->getPool($aSms);
        $aServices = $aSvsPools[$sPool];
        if (empty($aServices)) {
            return false;
        }
        $sServiceType = false;
        if (!empty($aSms[Const_Sms::F_SERVICETYPE]) && isset($aServices[$aSms[Const_Sms::F_SERVICETYPE]]) && !in_array($aSms[Const_Sms::F_SERVICETYPE], $aTryServices)) {
            $sServiceType = $aSms[Const_Sms::F_SERVICETYPE];
        } else {
            foreach ($aServices as $sService => $aService) {
                if (!in_array($sService, $aTryServices)) {
                    $sServiceType = $sService;
                    break;
                }
            }
        }
        return $sServiceType;
    }

    protected function getPool(&$aSms) {
        $iType = isset($aSms[Const_Sms::F_TYPE]) ? $aSms[Const_Sms::F_TYPE] : 0;
        if ($iType >= 8) {
            $sPool = Const_Sms::C_POOL_HIGH;
        } else {
            $sPool = Const_Sms::C_POOL_LOW;
        }
        return $sPool;
    }
    
    protected function getSmsCon(&$aSms) {
        if (isset($aSms[Const_Sms::F_CONTENT])) {
            return $aSms[Const_Sms::F_CONTENT];
        }
        $sSmsTemp = $aSms[Const_Sms::F_SMSTEMPLATE];
        if (empty($sSmsTemp)) {
            return false;
        }
        $aSmsTemp = Store_SmsTemp::getIns()->get($sSmsTemp);
        if (empty($aSmsTemp)) {
            return false;
        }
        $aParams = array();
        $aSmsParams = json_decode($aSms[Const_Sms::F_SMSPARAMS], true);
        foreach ($aSmsParams[Const_Sms::P_PARAMS] as $k => $v) {
            $aParams['{$' . $k . '}'] = $v;
        }
        $sCon = str_replace(array_keys($aParams) , array_values($aParams) , $aSmsTemp[Const_SmsTemp::F_TEMP]);
        return $sCon;
    }
    
    protected function loadServicePools() {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        $aServices = Store_Sms::getIns()->getService();
        foreach ($aServices as $sSvsName => $sService) {
            $aService = json_decode($sService, true);
            if (intval($aService[Const_Sms::C_SERVICE_SCORE]) < 0) {
                unset($aService[$sSvsName]);
            } else {
                $aServices[$sSvsName] = $aService;
            }
        }
        uasort($aServices, function ($a, $b) {
            if ($a[Const_Sms::C_SERVICE_SCORE] > $b[Const_Sms::C_SERVICE_SCORE]) {
                return 1;
            }
            return 0;
        });
        $aPools = array();
        foreach ($aServices as $sSvsName => $aService) {
            if (empty($aService[Const_Sms::C_SERVICE_POOL]) || !isset($this->aPoolChannels[$aService[Const_Sms::C_SERVICE_POOL]])) {
                continue;
            }
            $aPools[$aService[Const_Sms::C_SERVICE_POOL]][$sSvsName] = $aService;
        }
        $this->aSvsPools = $aPools;
        return $aPools;
    }
}
