<?php
/**
 * Document: MailServer
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailServer extends Task_Base {
    
    protected function main() {
        Util::output('begin to listen request from clients', 'notice');
        $this->server();
    }
    
    protected function server() {
        $oTask = $this->oTask->channel(Const_Task::C_MAILSERVER);
        while ($sMsg = $oTask->recv()) {
            $aMsg = json_decode($sMsg, true);
            if (empty($aMsg)) {
                continue;
            }
            Util::output($aMsg, 'debug');
            $iMailId = $this->addMail($aMsg);
            Util::output('new mail id: ' . $iMailId, 'notice');
            $this->queue($iMailId);
        }
        return true;
    }
    
    protected function addMail($aMail) {
        $oHMail = Store_Mail::getIns();
        $oHMail->{Const_Mail::F_CTIME} = time();
        return $oHMail->set($aMail);
    }
    
    protected function queue($id) {
        Queue_Mail::getIns()->wait($id, time())->add();
    }
    
}