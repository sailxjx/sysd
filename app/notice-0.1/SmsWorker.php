<?php
class SmsWorker extends Task_Base {
    protected $iScale = 2;
    
    protected function main() {
        $this->work();
    }
    
    protected function work() {
        $oTask = $this->oTask;
        $oQSms = Queue_Sms::getIns();
        $iDivider = $this->iScale + 1;
        $i = 0;
        while (1) {
            if ($i > 100000) {
                $i = 0;
            }
            $i++;
            if ($i % $iDivider) {
                $sMsg = $oTask->channel(Const_Task::C_SMSLIST_HIGH)->recv(true);
            } else {
                $sMsg = $oTask->channel(Const_Task::C_SMSLIST_LOW)->recv(true);
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
