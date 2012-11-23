<?php
class MailHeartbeat extends Base {
    
    protected $iSleep = 300; // 300 seconds
    protected $oRedis;
    
    protected function __construct() {
        parent::__construct();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function main() {
        $this->recvMail();
        // while (1) {
        //     $this->heartbeat();
        //     sleep($this->iSleep);
        // }
    }

    protected function recvMail() {
        $oPOPMail = new Mod_POPMail('pop.163.com', 110, 'heartbeat51fanli', '123456abc');
    }
    
    protected function heartbeat() {
        $this->getMailTemp();
    }
    
    protected function getMailTemp() {
        $sMailTemp = $this->oRedis->hget(Redis_Key::mailTemplates() , 'safequestion');
        $aParams = array(
            'username' => 'jxxu',
            'date' => date('Y-m-d') ,
            'code' => rand(1, 9999)
        );
        extract($aParams);
        eval("\$sMail = <<<EOF\n".$sMailTemp. "\nEOF;\n");
        return $sMail;
    }
    
    protected function loadMailBoxes() {
        return $this->oRedis->smembers(Redis_Key::mailSysBoxes());
    }
    
}
