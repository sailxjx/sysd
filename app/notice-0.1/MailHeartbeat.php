<?php
class MailHeartbeat extends Base {
    
    protected $iSleep = 300; // 300 seconds
    protected $oRedis;
    protected $aMailBoxes;
    protected $sMailHbTitle = 'Heartbeat Mail';
    protected $aMailHbCon;
    protected $aServiceErrors;
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
            sleep($this->getHbIntval());
            $this->hbRecv();
            $this->dealServices();
        }
        return true;
    }
    
    protected function recvMail() {
        $oPOPMail = new Mod_POPMail('pop.163.com', 110, 'heartbeat51fanli@163.com', '123456abc');
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
                    Const_Mail::F_CONTENT => $sCon,
                    Const_Mail::F_CTIME => $iTime,
                    Const_Mail::F_EXTRA => Const_Mail::EXTRA_HEARTBEAT,
                    Const_Mail::F_SERVICETYPE => $sService,
                );
                $aMail[Const_Mail::F_MAILPARAMS] = json_encode(array(
                    'channel' => $sService,
                    'address' => $sAddress,
                    'sendtime' => $iTime,
                    'content' => $sCon
                ));
                $iMailId = $oSMail->set($aMail);
                $oQMail->wait($iMailId, $iTime)->add();
                $this->aSendMails[] = $iMailId;
            }
        }
    }
    
    protected function getContent($iTime, $sAddress, $sService) {
        return md5($iTime . $sAddress . $sService);
    }
    
    protected function getMailParams() {
        return array();
    }
    
    protected function getMailHbParams() {
        $aParams = array();
    }
    
    protected function hbRecv() {
        $aMailBoxes = $this->aMailBoxes;
        $oSMail = Store_Mail::getIns();
        $aPopMails = $this->recvMails();
        foreach ($this->aSendMails as $iIndex => $iMailId) {
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
            foreach ($aPopMails as $sPopMailCon) {
                if (strpos($sPopMailCon, $sMailCon)!==false) {
                    Util::output("mail {$iMailId} checked succ");
                    $this->aServiceErrors[$sChannel] = 0;
                    break;
                }
            }
            if(isset($this->aSendMails[$iIndex])){
                $this->aServiceErrors[$sChannel]++;
            }
            unset($this->aSendMails[$iIndex]);
        }
        return true;
    }

    protected function dealServices(){
        $aMailServices = $this->loadServices();
        foreach ($this->aServiceErrors as $sService=>$iErrorTimes) {
            if($iErrorTimes > $this->iMaxError){
                $aMailServices[$sService]['score'] = -1;
                $this->oRedis->hset(Redis_Key::mailServices(), $sService, json_encode($aMailServices[$sService]));
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
                foreach ($aList as $k => $sList) {
                    list($iMailId, $sLength) = explode(' ', $sList);
                    $aList[$k] = $iMailId;
                }
                $aMailDiff = empty($aReadMailIds[$sAddress])?array():$aReadMailIds[$sAddress];
                $aUnReadIds = array_diff($aList, $aMailDiff);
                $aRetrMail = array();
                foreach ($aUnReadIds as $iMailId) {
                    $aRetrMail[$iMailId] = $oPOPMail->retrMail($iMailId);
                }
                $aReadMailIds[$sAddress] = array_merge($aMailDiff, array_keys($aRetrMail));
                $aMails[$sAddress] = $aRetrMail;
            }
            catch(Exception $e) {
                Util::output($e->getMessage());
                continue;
            }
        }
        $this->aReadMailIds = $aReadMailIds;
        $this->oRedis->set(Redis_Key::mailPopReadIds(), json_encode($aReadMailIds));
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
        }
        $this->aMailServices = $aMailServices;
        return $this->aMailServices;
    }

    protected function getReadMailIds() {
        if(!isset($this->aReadMailIds)){
            $sReadMailIds = $this->oRedis->get(Redis_Key::mailPopReadIds());
            $this->aReadMailIds = empty($sReadMailIds)?array():json_decode($sReadMailIds,true);
        }
        return $this->aReadMailIds;
    }
    
}
