<?php
/**
 * Document: MailVent
 * Created on: 2012-9-6, 18:09:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailVent extends Task_Vent {
    
    protected $mVCs; // vent channels
    protected $aServices;
    protected $oRedis;
    
    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->vent();
    }
    
    protected function vent() {
        $oTask = $this->oTask;
        $oQMail = Queue_Mail::getIns();
        $i = 0;
        $aChannels = $this->loadChannels();
        $iCNum = count($aChannels);
        while (1) { // every loop will reload channel configs
            $this->aServices = $this->loadChannelSet();
            $aMsgs = $this->listen();
            foreach ($aMsgs as $sMsg => $iScore) {
                $aMail = $this->decMail($sMsg);
                if (empty($aMail[Const_Mail::F_SERVICETYPE])) {
                    $oQMail->move('wait', 'fail', $sMsg, time());
                } else {
                    if ($oQMail->move('wait', 'send', $sMsg, time())) {
                        $oTask->channel($aChannels[$i % $iCNum])->msg($sMsg)->send();
                        Util::output('sending msgs: ', $sMsg);
                        $i++;
                    }
                }
            }
            if ($i > 100000) { //reset i to 0
                $i = 0;
            }
        }
    }
    
    /**
     * pre set mail servicetype
     * set params in contents
     */
    protected function decMail($iMailId) {
        $oRedis = $this->oRedis;
        $aMail = Store_Mail::getIns()->get($iMailId, array(
            Const_Mail::F_ID,
            Const_Mail::F_MAILPARAMS,
            Const_Mail::F_CONTENT,
            Const_Mail::F_SERVICETYPE,
            Const_Mail::F_TRYSERVICE,
        ));
        $aMail[Const_Mail::F_SERVICETYPE] = $this->getRecommendServiceType($aMail);
        $aMail[Const_Mail::F_CONTENT] = $this->getMailCon($aMail);
        Store_Mail::getIns()->set($aMail);
        return $aMail;
    }
    
    protected function getRecommendServiceType(&$aMail) {
        $aServices = $this->aServices;
        $sServiceType = '';
        $aMailTryService = json_decode($aMail[Const_Mail::F_TRYSERVICE], true);
        $aMailTryService = empty($aMailTryService) ? array() : $aMailTryService;
        if (!empty($aMail[Const_Mail::F_SERVICETYPE]) && isset($aServices[$aMail[Const_Mail::F_SERVICETYPE]]) && !in_array($aMail[Const_Mail::F_SERVICETYPE], $aMailTryService)) { // has servicetype and servicetype is available
            $sServiceType = $aMail[Const_Mail::F_SERVICETYPE];
        } else {
            foreach ($aServices as $sService => $aService) {
                if (!in_array($sService, $aMailTryService)) {
                    $sServiceType = $sService;
                    break;
                }
            }
        }
        return $sServiceType;
    }
    
    protected function getMailCon(&$aMail) {
        $aServices = $this->aServices;
        if (empty($aMail[Const_Mail::F_SERVICETYPE]) || !isset($aServices[$aMail[Const_Mail::F_SERVICETYPE]])) {
            return false;
        }
        $aService = $aServices[$aMail[Const_Mail::F_SERVICETYPE]];
        $sMailCon = '';
        switch ($aService[Const_Mail::C_SERVICE_TEMP]) {
            case Const_Mail::TEMP_LOCAL:
                $sMailCon = $this->buildMailConFromLocal($aMail);
            break;
            case Const_Mail::TEMP_REMOTE:
                $sMailCon = $this->buildMailConFromRemote($aMail);
            break;
            default:
            break;
        }
        return $sMailCon;
    }
    
    protected function buildMailConFromLocal(&$aMail) {
        return $aMail[Const_Mail::F_MAILPARAMS];
    }
    
    protected function buildMailConFromRemote(&$aMail) {
        return $aMail[Const_Mail::F_MAILPARAMS];
    }
    
    protected function loadChannelSet() {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        $sChanKey = Redis_Key::mailServices();
        $aServices = $oRedis->hgetall($sChanKey);
        foreach ($aServices as $k => $v) {
            $aV = json_decode($v, true);
            if (intval($aV[Const_Mail::C_SERVICE_SCORE]) <= 0) {
                unset($aServices[$k]);
            } else {
                $aServices[$k] = $aV;
            }
        }
        uasort($aServices, function ($a, $b) {
            if ($a[Const_Mail::C_SERVICE_SCORE] > $b[Const_Mail::C_SERVICE_SCORE]) {
                return 1;
            }
            return 0;
        });
        return $aServices;
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
                    $sVC = Redis_Key::convKeyToFunc($sVC);
                    for ($i = 0;$i < $iWeight;$i++) {
                        $this->mVCs[] = $sVC;
                    }
                }
            }
        }
        return $this->mVCs;
    }
}
