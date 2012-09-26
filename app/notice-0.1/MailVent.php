<?php
/**
 * Document: MailVent
 * Created on: 2012-9-6, 18:09:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailVent extends Task_Vent {
    
    protected $mVCs; // vent channels
    
    protected function main() {
        $this->vent();
    }
    
    protected function vent() {
        $sModClass = $this->sModClass;
        $oTask = $sModClass::getIns();
        $oQMail = Queue_Mail::getIns();
        $i = 0;
        $aChannels = $this->loadChannels();
        $iCNum = count($aChannels);
        while (1) {
            $aMsgs = $this->listen();
            foreach ($aMsgs as $sMsg => $iScore) {
                $oQMail->move('wait', 'send', $sMsg, time());
                $oTask->channel($aChannels[$i % $iCNum])->msg($sMsg)->send();
                Util::output('sending msgs: ', $sMsg);
                $i++;
            }
            if ($i > 100000) { //reset i to 0
                $i = 0;
            }
        }
    }
    
    protected function listen() {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        while (!$aMsgs = $oRedis->zrangebyscore(Redis_Key::mailWait() , '-inf', time() , array(
            'withscores' => true
        ))) {
            usleep(10000);
        }
        return $aMsgs;
    }
    
    protected function loadChannels() {
        if (!isset($this->mVCs)) {
            $oRedis = Fac_SysDb::getIns()->loadRedis();
            if (!($aVCs = $oRedis->zrange(Redis_Key::mailChannels() , 0, -1, true))) {
                $this->mVCs = array(
                    Const_Task::C_MAILLIST
                );
            } else {
                foreach ($aVCs as $sVC => $iWeight) {
                    $sVC=Redis_Key::convKeyToFunc($sVC);
                    for ($i = 0;$i < $iWeight;$i++) {
                        $this->mVCs[] = $sVC;
                    }
                }
            }
        }
        return $this->mVCs;
    }
}
