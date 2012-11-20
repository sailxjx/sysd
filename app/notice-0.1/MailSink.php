<?php
/**
 * collect sending status of mail
 * Document: MailSink
 * Created on: 2012-9-6, 18:10:08
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailSink extends Task_Sink {
    
    protected $iRemNum = 100;
    
    public function main() {
        $this->sink();
    }
    
    protected function sink() {
        $oTask = $this->oTask->channel(Const_Task::C_MAILRESULT);
        $oQMail = Queue_Mail::getIns();
        while ($sMsg = $oTask->recv()) {
            Util::output('recv msg: ' . $sMsg);
            list($iMailId, $sTo) = explode($this->sSplite, $sMsg);
            $this->setTryService($iMailId);
            $oQMail->move('send', $sTo, $iMailId, time());
        }
    }
    
    protected function setTryService($iMailId) {
        $oSMail = Store_Mail::getIns();
        $aMail = $oSMail->get($iMailId, array(
            Const_Mail::F_ID,
            Const_Mail::F_SERVICETYPE,
            Const_Mail::F_TRYSERVICE
        ));
        $aTryService = json_decode($aMail[Const_Mail::F_TRYSERVICE], true);
        $aTryService = empty($aTryService) ? array() : $aTryService;
        array_push($aTryService, $aMail[Const_Mail::F_SERVICETYPE]);
        $aTryService = array_filter(array_unique($aTryService));
        $aMail[Const_Mail::F_TRYSERVICE] = json_encode($aTryService);
        $oSMail->set($aMail);
        return $aMail;
    }
    
}
