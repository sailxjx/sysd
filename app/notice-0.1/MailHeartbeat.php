<?php
class MailHeartbeat extends Base {
    
    protected $iSleep = 300; // 300 seconds
    protected $oRedis;
    protected $aMailBoxes;
    protected $sHbMailTitle = 'Heartbeat Mail';
    protected $aHbMailCon;
    protected $aServiceErrors = array(
        'easeye' => 1,
        'webpower' => 2
    );
    
    protected function __construct() {
        parent::__construct();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function main() {
        print_r($this->loadServices());exit;
        $this->recvMail();
    }
    
    protected function recvMail() {
        $oPOPMail = new Mod_POPMail('pop.163.com', 110, 'heartbeat51fanli@163.com', '123456abc');
        print_r($oPOPMail->listMail());
    }
    
    protected function hbSend() {
        $aMailBoxes = $this->loadMailBoxes();
        foreach ($aMailBoxes as $sAddress => $aMailBox) {
            
        }
    }
    
    protected function gethbMailParams() {
        $aParams = array();
    }
    
    protected function hbRecv() {
        $aMailBoxes = $this->aMailBoxes;
    }
    
    protected function loadMailBoxes() {
        $aMailBoxes = $this->oRedis->hgetall(Redis_Key::hbMailBoxes());
        foreach ((array)$aMailBoxes as $sAddress => $sMailBox) {
            $aMailBoxes[$sAddress] = json_decode($sMailBox, true);
        }
        $this->aMailBoxes = $aMailBoxes;
        return $aMailBoxes;
    }

    protected function loadServices() {
        $aMailServices = $this->oRedis->hgetall(Redis_Key::mailServices());
        foreach ((array)$aMailServices as $sName=>$sMailService) {
            $aMailServices[$sName] = json_decode($sMailService, true);
        }
        $this->aMailServices = $aMailServices;
        return $this->aMailServices;
    }
    
}
