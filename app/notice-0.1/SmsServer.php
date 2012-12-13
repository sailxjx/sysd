<?php
class SmsServer extends Task_Base {

    protected function main() {
        Util::output('begin to listen request from clients', 'notice');
        $this->server();
    }

    protected function server() {
        $oTask = $this->oTask->channel(Const_Task::C_SMSSERVER);
        while ($sMsg = $oTask->recv()) {
            $aMsg = json_decode($sMsg, true);
            if (empty($aMsg)) {
                continue;
            }
            Util::output($aMsg, 'debug');
            $iSmsId = $this->addSms($aMsg);
            Util::output('new sms id: ' . $iSmsId, 'notice');
            $this->queue($iSmsId);
        }
        return true;
    }
    
    protected function addSms($aSms) {
        $oSSms = Store_Sms::getIns();
        $aSms[Const_Sms::F_CTIME] = time();
        return $oSSms->set($aSms);
    }
    
    protected function queue($iSmsId) {
        Queue_Sms::getIns()->wait($iSmsId, time())->add();
    }
}
