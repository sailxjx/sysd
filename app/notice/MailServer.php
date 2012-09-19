<?php
/**
 * Document: MailServer
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailServer extends Task_Base {
    
    protected function main() {
        for ($i = 0;$i < 10000;$i++) {
            $this->queue($this->addMail());
        }
        exit;
    }
    
    protected function addMail() {
        $oHMail = Store_Mail::getIns();
        $oHMail->{Const_Mail::F_SENDER} = 'web';
        $oHMail->{Const_Mail::F_RECEIVER} = 'me';
        return $oHMail->set();
    }
    
    protected function getMail($id) {
        $oHMail = Store_SiteMsg::getIns();
        return $oHMail->get($id);
    }
    
    protected function queue($id) {
        Queue_Mail::getIns()->wait($id, time()+100)->add();
    }
    
}