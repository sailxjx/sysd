<?php

class SmsWorker extends Task_Base {

    protected $aPoolChannels = array(
        Const_Sms::C_POOL_HIGH => Const_Task::C_SMSLIST_HIGH,
        Const_Sms::C_POOL_LOW => Const_Task::C_SMSLIST_LOW,
        Const_Sms::C_POOL_RETRY => Const_Task::C_SMSLIST_RETRY
    );
    
    protected function main() {
        $this->work();
    }
    
    protected function work() {
        $oTask = $this->oTask;
        $oQSms = Queue_Sms::getIns();
        $aParams = $this->oCore->getParams();
        $sPool = empty($aParams['pool'])?'high':$aParams['pool'];
        $iPoolIdx = isset($this->aPoolChannels[$sPool])?$this->aPoolChannels[$sPool]:Const_Task::C_SMSLIST_HIGH;
        while (1) {
            $sMsg = $oTask->channel($iPoolIdx)->recv();
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
