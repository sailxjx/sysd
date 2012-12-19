<?php

class SmsWorker extends Task_Base {
    protected $aPoolWeight = array(
        Const_Sms::C_POOL_HIGH => 3,
        Const_Sms::C_POOL_LOW => 1,
        Const_Sms::C_POOL_RETRY => 1,
    );
    protected $aPoolChannels = array(
        Const_Sms::C_POOL_HIGH => Const_Task::C_SMSLIST_HIGH,
        Const_Sms::C_POOL_LOW => Const_Task::C_SMSLIST_LOW,
        Const_Sms::C_POOL_RETRY => Const_Task::C_SMSLIST_RETRY
    );
    protected $iScale = 2;
    
    protected function main() {
        $this->work();
    }
    
    protected function work() {
        $oTask = $this->oTask;
        $oQSms = Queue_Sms::getIns();
        $aPools = $this->getPools();
        $iDivider = count($aPools);
        $i = 0;
        while (1) {
            if ($i > 100000) {
                $i = 0;
            }
            $iIdx = $i%$iDivider;
            $i++;
            if (isset($aPools[$iIdx]) && isset($this->aPoolChannels[$aPools[$iIdx]])) {
                $sMsg = $oTask->channel($this->aPoolChannels[$aPools[$iIdx]])->recv(true);
            }
            if (empty($sMsg)) {
                usleep($this->iInterval);
                continue;
            }
            Util::output('recv sms: ' . $sMsg);
            if ($this->sendSms($sMsg)) {
                $oTask->channel(Const_Task::C_SMSRESULT)->msg($sMsg . $this->sSplite . 'succ')->send();
                Util::output('succ sms: ' . $sMsg);
            } else {
                $oTask->channel(Const_Task::C_SMSRESULT)->msg($sMsg . $this->sSplite . 'error')->send();
                Util::output('error sms: ' . $sMsg);
            }
        }
        
        return true;
    }

    protected function getPools() {
        $aPoolWeight = $this->aPoolWeight;
        $aPools = array();
        foreach ($aPoolWeight as $sPool=>$iWeight) {
            for ($i=0; $i < $iWeight; $i++) { 
                $aPools[] = $sPool;
            }
        }
        return $aPools;
    }
    
    protected function sendSms($iSmsId) {
        $aSms = Store_Sms::getIns()->get($iSmsId);
        if (empty($aSms[Const_Sms::F_MOBILE]) || empty($aSms[Const_Sms::F_CONTENT]) || empty($aSms[Const_Sms::F_SERVICETYPE])) {
            
            return false;
        }
        $sMethod = 'send' . ucfirst($aSms[Const_Sms::F_SERVICETYPE]);
        $sFunc = "Util_SmsSender::" . $sMethod;
        if (!method_exists('Util_SmsSender', $sMethod)) {
            
            return false;
        }
        return call_user_func($sFunc, $aSms);
    }
    
}
