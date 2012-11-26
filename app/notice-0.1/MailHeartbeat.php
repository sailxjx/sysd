<?php
class MailHeartbeat extends Base {
    
    protected $oRedis;
    protected $aMailBoxes;
    protected $sMailHbTitle = 'Heartbeat Mail';
    protected $aMailHbCon;
    protected $aMailServices;
    protected $iMaxError = 3;
    protected $aSendMails;
    protected $aReadMailIds;
    
    protected function __construct() {
        parent::__construct();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function main() {
        while (true) {
            $this->hbSend();
            sleep($this->getHbInterval());
            $this->hbRecv();
            $this->dealServices();
        }
        return true;
    }
    
    protected function hbSend() {
        $aMailBoxes = $this->loadMailBoxes();
        $aMailServices = $this->loadServices();
        $oSMail = Store_Mail::getIns();
        $oQMail = Queue_Mail::getIns();
        foreach ($aMailBoxes as $sAddress => $aMailBox) {
            foreach ($aMailServices as $sService => $aService) {
                $iTime = time();
                $sCon = $this->getContent($iTime, $sAddress, $sService);
                $aMail = array(
                    Const_Mail::F_SENDER => 'sysd',
                    Const_Mail::F_RECEIVER => 'heartbeat',
                    Const_Mail::F_EMAIL => $sAddress,
                    Const_Mail::F_TITLE => $this->sMailHbTitle,
                    Const_Mail::F_CTIME => $iTime,
                    Const_Mail::F_EXTRA => Const_Mail::EXTRA_HEARTBEAT,
                    Const_Mail::F_SERVICETYPE => $sService,
                    Const_Mail::F_MAILTEMPLATE => 'heartbeat'
                );
                $aMail[Const_Mail::F_MAILPARAMS] = json_encode(array(
                    'channel' => $sService,
                    'address' => $sAddress,
                    'sendtime' => $iTime,
                    'content' => $sCon,
                    'params' => array(
                        'hbcode' => $sCon
                    )
                ));
                $iMailId = $oSMail->set($aMail);
                $oQMail->wait($iMailId, $iTime)->add();
                $this->aSendMails[] = $iMailId;
                Util::output("heartbeat mail: {$sService},{$iMailId}");
            }
        }
    }
    
    protected function getContent($iTime, $sAddress, $sService) {
        return md5($iTime . $sAddress . $sService);
    }
    
    protected function hbRecv() {
        $aMailBoxes = $this->aMailBoxes;
        $oSMail = Store_Mail::getIns();
        $aPopMails = $this->recvMails();
        $aSendMails = empty($this->aSendMails)?array():$this->aSendMails;
        $aMailServices = $this->loadServices();
        $bSaveService = false;
        foreach ($aSendMails as $iIndex => $iMailId) {
            $aMail = $oSMail->get($iMailId);
            $aMailParams = json_decode($aMail[Const_Mail::F_MAILPARAMS], true);
            $aMailBox = $aMailBoxes[$aMailParams['address']];
            if (empty($aMailBox)) {
                trigger_error('mail box is empty or changed!', E_USER_WARNING);
                unset($iIndex);
                continue;
            }
            $sMailCon = $aMailParams['content'];
            $sAddress = $aMailParams['address'];
            $sChannel = $aMailParams['channel'];
            $aAddrPopMails = isset($aPopMails[$sAddress]) ? $aPopMails[$sAddress] : array();
            foreach ($aAddrPopMails as $sPopMailCon) {
                if (strpos($sPopMailCon, $sMailCon) !== false) {
                    Util::output("mail {$iMailId} checked succ");
                    $aMailServices[$sChannel][Const_Mail::C_SERVICE_ERRTIMES] = 0;
                    $bSaveService = true;
                    break;
                }
            }
            if (isset($this->aSendMails[$iIndex])) {
                Mod_Log::getIns()->warning('MAILHEARTBEAT: [%t]; "%m"; %d; %c;', date('Y-m-d H:i:s') , "Channel {$sChannel} is missing a mail: {$iMailId}", '{}', Const_Log::POS_HEARTBEAT);
                $aMailServices[$sChannel][Const_Mail::C_SERVICE_ERRTIMES] = 
                isset($aMailServices[$sChannel][Const_Mail::C_SERVICE_ERRTIMES])?($aMailServices[$sChannel][Const_Mail::C_SERVICE_ERRTIMES]+1):1;
                $bSaveService = true;
            }
            unset($this->aSendMails[$iIndex]);
        }
        if($bSaveService){
            foreach ($aMailServices as $sService=>$aService) {
                $this->oRedis->hset(Redis_Key::mailServices(), $sService, json_encode($aService));
            }
        }
        return true;
    }
    
    protected function dealServices() {
        $aMailServices = $this->loadServices();
        foreach ($aMailServices as $sService => $aService) {
            if (isset($aService[Const_Mail::C_SERVICE_ERRTIMES]) && $aService[Const_Mail::C_SERVICE_ERRTIMES] > $this->iMaxError) {
                Mod_Log::getIns()->error('MAILHEARTBEAT: [%t]; "%m"; %d; %c;', date('Y-m-d H:i:s') , "Channel {$sService} has been turn down by heartbeat", '{}', Const_Log::POS_HEARTBEAT);
                $aMailServices[$sService]['score'] = - 1;
                $this->oRedis->hset(Redis_Key::mailServices() , $sService, json_encode($aMailServices[$sService]));
            }
        }
        $this->aMailServices = $aMailServices;
        return true;
    }
    
    protected function recvMails() {
        $aMailBoxes = $this->aMailBoxes;
        $aReadMailIds = $this->getReadMailIds();
        $aMails = array();
        foreach ((array)$aMailBoxes as $sAddress => $aMailBox) {
            try {
                $oPOPMail = new Mod_POPMail($aMailBox['server'], $aMailBox['port'], $aMailBox['user'], $aMailBox['pass']);
                $aList = $oPOPMail->listMail();
                $aMailDiff = empty($aReadMailIds[$sAddress]) ? array() : $aReadMailIds[$sAddress];
                $aUnReadIds = array_diff($aList, $aMailDiff);
                $aRetrMail = array();
                foreach ($aUnReadIds as $sMailId) {
                    list($iMailId, $iLength) = explode(' ', $sMailId);
                    $aRetrMail[$iMailId] = $oPOPMail->retrMail($iMailId);
                }
                $aReadMailIds[$sAddress] = array_unique(array_merge($aMailDiff, $aList));
                $aMails[$sAddress] = $aRetrMail;
                $this->oRedis->hincrby(Redis_Key::mailHbSendStatus(), 'succ_'.date('Y-m-d_H'), 1);
            }
            catch(Exception $e) {
                Util::output($e->getMessage());
                $this->oRedis->hincrby(Redis_Key::mailHbSendStatus(), 'fail_'.date('Y-m-d_H'), 1);
                return $this->recvMails();
            }
        }
        $this->aReadMailIds = $aReadMailIds;
        $this->oRedis->set(Redis_Key::mailPopReadIds() , json_encode($aReadMailIds));
        return $aMails;
    }

    protected function loadMailBoxes() {
        $aMailBoxes = $this->oRedis->hgetall(Redis_Key::mailHbBoxes());
        foreach ((array)$aMailBoxes as $sAddress => $sMailBox) {
            $aMailBoxes[$sAddress] = json_decode($sMailBox, true);
        }
        $this->aMailBoxes = $aMailBoxes;
        return $aMailBoxes;
    }
    
    protected function loadServices() {
        $aMailServices = $this->oRedis->hgetall(Redis_Key::mailServices());
        foreach ((array)$aMailServices as $sName => $sMailService) {
            $aMailServices[$sName] = json_decode($sMailService, true);
            if ($aMailServices[$sName][Const_Mail::C_SERVICE_SCORE] < 0) {
                unset($aMailServices[$sName]);
            }
        }
        $this->aMailServices = $aMailServices;
        return $this->aMailServices;
    }
    
    protected function getReadMailIds() {
        if (!isset($this->aReadMailIds)) {
            $sReadMailIds = $this->oRedis->get(Redis_Key::mailPopReadIds());
            $this->aReadMailIds = empty($sReadMailIds) ? array() : json_decode($sReadMailIds, true);
        }
        return $this->aReadMailIds;
    }
    
    protected function getHbInterval() {
        return $this->oRedis->get(Redis_Key::mailHbInterval());
    }
    
}
