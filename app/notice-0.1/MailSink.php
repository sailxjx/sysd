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
            $oQMail->move('send', $sTo, $iMailId, time());
        }
    }
}
