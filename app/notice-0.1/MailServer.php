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
            $this->decMail($aMsg);
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

    protected function decMail(&$aMail) {
        $aMailParams = json_decode($aMail[Const_Mail::F_MAILPARAMS], true);
        if (isset($aMailParams[Const_Mail::P_PARAMS]) && is_array($aMailParams[Const_Mail::P_PARAMS])) {
            $aMailParams = array_merge($aMailParams,$aMailParams[Const_Mail::P_PARAMS]);
            unset($aMailParams[Const_Mail::P_PARAMS]);
            $aMail[Const_Mail::F_MAILPARAMS] = json_encode($aMailParams);
        }
        return $aMail;
    }
    
}