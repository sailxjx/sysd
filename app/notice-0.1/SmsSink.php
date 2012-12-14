<?php
class SmsSink extends Task_Base {
    protected function main() {
        $this->sink();
    }
    
    protected function sink() {
        $oTask = $this->oTask->channel(Const_Task::C_SMSRESULT);
        $oQSms = Queue_Sms::getIns();
        while ($sMsg = $oTask->recv()) {
            Util::output('recv sms: ' . $sMsg, 'notice');
            list($iSmsId, $sTo) = explode($this->sSplite, $sMsg);
            $aSms = $this->setTryService($iSmsId);
            $oQSms->move('send', $sTo, $iSmsId, time());
        }
    }
    
    protected function setTryService($iSmsId) {
        $oSSms = Store_Sms::getIns();
        $aSms = $oSSms->get($iSmsId, array(
            Const_Sms::F_ID,
            Const_Sms::F_SERVICETYPE,
            Const_Sms::F_TRYSERVICE
        ));
        $aTryService = json_decode($aSms[Const_Sms::F_TRYSERVICE], true);
        $aTryService = empty($aTryService) ? array() : $aTryService;
        array_push($aTryService, $aSms[Const_Sms::F_SERVICETYPE]);
        $aTryService = array_filter(array_unique($aTryService));
        $aSms[Const_Sms::F_TRYSERVICE] = json_encode($aTryService);
        $oSSms->set($aSms);
        return $aSms;
    }
}
