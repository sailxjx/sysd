<?php
/**
 * redeliver mails from error queue
 */
class MailRedel extends Task_Base {
    
    protected $iRemNum = 100;
    
    public function main() {
        $this->redel();
    }
    
    protected function redel() {
        $oQMail = Queue_Mail::getIns();
        $oRMail = Rule_Mail::getIns();
        while (1) {
            $aMsgs = $this->listen();
            foreach ($aMsgs as $sMsg) { //sMsg == $iMailId
                Util::output('redeliver msg: ' . $sMsg, 'notice');
                if (($iRdTime = $oRMail->redeliver($sMsg)) === false) {
                    $oQMail->move('error', 'fail', $sMsg, time());
                } else {
                    if($oQMail->move('error', 'wait', $sMsg, time() + $iRdTime)){
                        $oRMail->incrRedel($sMsg);
                    }
                }
            }
        }
    }
    
    protected function listen() {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        while (!$aMsgs = $oRedis->zrange(Redis_Key::mailError() , 0, $this->iRemNum)) {
            usleep($this->iInterval);
        }
        return $aMsgs;
    }
}
