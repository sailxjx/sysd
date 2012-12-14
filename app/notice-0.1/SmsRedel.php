<?php
class SmsRedel extends Task_Base {
    protected $iRemNum = 100;
    protected $oRedis;
    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->redel();
    }
    
    protected function redel() {
        $oQSms = Queue_Sms::getIns();
        $oRSms = Rule_Sms::getIns();
        while (1) {
            $aMsgs = $this->listen();
            foreach ($aMsgs as $sMsg) {
                Util::output('redeliver sms: ' . $sMsg, 'notice');
                if (($iRdTime = $oRSms->redeliver($sMsg)) === false) {
                    $oQSms->move('error', 'fail', $sMsg, time());
                } else {
                    if ($oQSms->move('error', 'wait', $sMsg, time() + $iRdTime)) {
                        $oRSms->incrRedel($sMsg);
                    }
                }
            }
        }
    }
    
    protected function listen() {
        $oRedis = $this->oRedis;
        while (!$aMsgs = $oRedis->zrange(Redis_Key::smsError() , 0, $this->iRemNum)) {
            usleep($this->iInterval);
        }
        return $aMsgs;
    }
}
