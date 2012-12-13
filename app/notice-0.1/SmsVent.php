<?php
class SmsVent extends Task_Base {
    protected $oRedis;
    protected $aPools = array(
        Const_Task::C_SMSLIST_H,
        Const_Task::C_SMSLIST_L
    );

    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->vent();
    }
    
    protected function vent() {
        $oTask = $this->oTask;
        while (1) {
            $aMsgs = $this->listen();
            foreach ($aMsgs as $iSmsId => $iScore) {
                $aSms = $this->decSms($iSmsId);
            }
        }
    }
    
    protected function listen() {
        $oRedis = $this->oRedis;
        while (!$aMsgs = $oRedis->zrangebyscore(Redis_Key::smsWait() , '-inf', time() , array(
            'withscores' => true
        ))) {
            usleep($this->iInterval);
        }
        return $aMsgs;
    }

    protected function decSms($iSmsId) {
        $aSms = Store_Sms::getIns()->get($iSmsId);
        $this->getSmsCon($iSmsId);
    }
}
