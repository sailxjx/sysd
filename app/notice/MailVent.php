<?php

/**
 * Document: MailVent
 * Created on: 2012-9-6, 18:09:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailVent extends Task_Vent {

    protected function main() {
        $this->vent();
    }

    protected function vent() {
        $sModClass=$this->sModClass;
        $oTask = $sModClass::getIns()->channel(0);
        $oQMail = Queue_Mail::getIns();
        while(1){
            $aMsgs = $this->listen();
            foreach ($aMsgs as $sMsg => $iScore) {
                $oQMail->move('wait','send',$sMsg,time());
                $oTask->msg($sMsg);
            }
            Util::output('sending msgs: ',$aMsgs);
            $oTask->send();
        }
    }

    protected function listen(){
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        while(!$aMsgs = $oRedis->zrangebyscore(Redis_Key::mailWait(), '-inf', time(), array('withscores' => true))){
            usleep(10000);
        }
        return $aMsgs;
    }

}
