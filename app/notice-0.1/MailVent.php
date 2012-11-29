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
            $aMsgs = $this->listen();
            $this->aServices = $this->loadChannelSet();
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
        $aMail = Store_Mail::getIns()->get($iMailId);
        if (empty($aMail[Const_Mail::F_EXTRA]) || $aMail[Const_Mail::F_EXTRA] != Const_Mail::EXTRA_HEARTBEAT) {
            $aMail[Const_Mail::F_SERVICETYPE] = $this->getRecommendServiceType($aMail);
        }
        $this->getMailCon($aMail);
        Store_Mail::getIns()->set($aMail);
        return $aMail;
    }
    
    protected function getRecommendServiceType(&$aMail) {
        $aServices = $this->aServices;
        $sServiceType = '';
        $aMailTryService = isset($aMail[Const_Mail::F_TRYSERVICE]) ? json_decode($aMail[Const_Mail::F_TRYSERVICE], true): array();
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
        $sMailTitle = '';
        switch ($aService[Const_Mail::C_SERVICE_TEMP]) {
            case Const_Mail::TEMP_LOCAL:
                $this->buildMailConFromLocal($aMail);
            break;
            case Const_Mail::TEMP_REMOTE:
                $this->buildMailConFromRemote($aMail);
            break;
            default:
            break;
        }
        return $aMail[Const_Mail::F_CONTENT];
    }

    protected function buildMailConFromLocal(&$aMail) {
        $sMailTemp = $aMail[Const_Mail::F_MAILTEMPLATE];
        $aMailTemp = Store_MailTemp::getIns()->get($sMailTemp);
        $aMailParams = json_decode($aMail[Const_Mail::F_MAILPARAMS], true);
        if (empty($aMailTemp)) {
            return false;
        }
        if(empty($aMailTemp[Const_MailTemp::F_TITLE])) {
            $sMailTempTitle = empty($aMail[Const_Mail::F_TITLE])?'返利网邮件':$aMail[Const_Mail::F_TITLE];
        }else{
            $sMailTempTitle = $aMailTemp[Const_MailTemp::F_TITLE];
        }
        $aParams = array();
        foreach ($aMailParams[Const_Mail::P_PARAMS] as $k => $v) {
            $aParams['{$'.$k.'}'] = $v;
        }
        $aMail[Const_Mail::F_CONTENT] = str_replace(array_keys($aParams), array_values($aParams), $aMailTemp[Const_MailTemp::F_TEMP]);
        $aMail[Const_Mail::F_TITLE] = str_replace(array_keys($aParams), array_values($aParams), $sMailTempTitle);
        return true;
    }
    
    protected function buildMailConFromRemote(&$aMail) {
        $sMailTemp = $aMail[Const_Mail::F_MAILTEMPLATE];
        $aMailTemp = Store_MailTemp::getIns()->get($sMailTemp);
        $aMailCon = json_decode($aMail[Const_Mail::F_MAILPARAMS], true);
        if (empty($aMailTemp)) {
            return false;
        }
        list($aMailCon[Const_Mail::P_CAMPAIGNID], $aMailCon[Const_Mail::P_GROUPID], $aMailCon[Const_Mail::P_MAILINGID]) = explode(',', $aMailTemp[Const_MailTemp::F_WEBPOWERID]);
        $aMail[Const_Mail::F_CONTENT] = json_encode($aMailCon);
        return true;
    }
    
    protected function loadChannelSet() {
        $oRedis = Fac_SysDb::getIns()->loadRedis();
        $sChanKey = Redis_Key::mailServices();
        $aServices = $oRedis->hgetall($sChanKey);
        foreach ($aServices as $k => $v) {
            $aV = json_decode($v, true);
            if (intval($aV[Const_Mail::C_SERVICE_SCORE]) < 0) {
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
            usleep(100000);
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
