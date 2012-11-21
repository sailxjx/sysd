<?php
class MailDataInit extends Base {
    protected $oRedis;
    protected $aServices = array(
        'easeye' => array(
            'score' => 1,
            'temp' => 'local'
        ) ,
        'huiyee' => array(
            'score' => 5,
            'temp' => 'local'
        ) ,
        'webpower' => array(
            'score' => 3,
            'temp' => 'remote'
        ) ,
        'webpower_mass' => array(
            'score' => - 1,
            'temp' => 'remote'
        )
    );
    
    protected $aSysMailBoxes = array(
        'jingxin.xu@51fanli.com'
    );
    
    protected function __construct() {
        parent::__construct();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function main() {
        $this->initServices();
        $this->initSysMailBoxes();
        $this->initMailTemps();
    }
    
    /**
     * init mail service sets ( or so called channels )
     *
     */
    protected function initServices() {
        $sServiceKey = Redis_Key::mailServices();
        $oRedis = $this->oRedis;
        foreach ($this->aServices as $sService => $aService) {
            $oRedis->hset($sServiceKey, $sService, json_encode($aService));
        }
        return true;
    }
    
    /**
     * init system mail boxes to send heartbeat mails
     *
     */
    protected function initSysMailBoxes() {
        $sSysMailBoxKey = Redis_Key::mailSysBoxes();
        foreach ($this->aSysMailBoxes as $sMailBox) {
            $this->oRedis->sadd($sSysMailBoxKey, $sMailBox);
        }
        return true;
    }
    
    /**
     * init mail templates ( set mail templates into redis )
     *
     */
    protected function initMailTemps() {
        global $G_LOAD_PATH;
        $sNoticePath = '';
        foreach ($G_LOAD_PATH as $sPath) {
            if (strpos($sPath, 'notice') !== false) {
                $sNoticePath = $sPath;
            }
        }
        if (empty($sNoticePath)) {
            return false;
        }
        $sMailTempPath = $sNoticePath . 'tmp/mailtemp/*';
        $aMailTempFiles = glob($sMailTempPath);
        $sMailTempKey = Redis_Key::mailTemplates();
        $oSMailTemp = Store_MailTemp::getIns();
        foreach ((array)$aMailTempFiles as $sMailTempFile) {
            $sMailTemp = file_get_contents($sMailTempFile);
            $aMailDirs = explode('/', $sMailTempFile);
            $sMailTempName = array_pop($aMailDirs);
            list($sMailTempName, $sMailExtName) = explode('.', $sMailTempName);
            $iMailId = $oSMailTemp->set(array(
                Const_MailTemp::F_NAME => $sMailTempName,
                Const_MailTemp::F_TEMP => $sMailTemp,
                Const_MailTemp::F_CTIME => date('Y-m-d H:i:s') ,
                Const_MailTemp::F_INUSE => 1
            ));
            $this->oRedis->sadd($sMailTempKey, $iMailId);
        }
        return true;
    }
    
}
