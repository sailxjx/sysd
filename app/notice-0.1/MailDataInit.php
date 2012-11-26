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
    
    protected $aHbMailBoxes = array(
        'heartbeat51fanli@163.com' => array(
            'server' => 'pop.163.com',
            'port' => '110',
            'user' => 'heartbeat51fanli',
            'pass' => '123456abc'
        ),
    );

    protected $iHbMailInterval = 300;
    
    protected function __construct() {
        parent::__construct();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function main() {
        $this->initServices();
        $this->initHeartbeatMailBoxes();
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
    protected function initHeartbeatMailBoxes() {
        $sSysMailBoxKey = Redis_Key::mailHbBoxes();
        foreach ($this->aHbMailBoxes as $sMailAddress => $aMailBox) {
            $this->oRedis->hset($sSysMailBoxKey, $sMailAddress, json_encode($aMailBox));
        }
        $this->oRedis->set(Redis_Key::mailHbInterval(), $this->iHbMailInterval);
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
        $oSMailTemp = Store_MailTemp::getIns();
        foreach ((array)$aMailTempFiles as $sMailTempFile) {
            $sMailTemp = file_get_contents($sMailTempFile);
            $aMailDirs = explode('/', $sMailTempFile);
            $sMailTempName = array_pop($aMailDirs);
            list($sMailTempName, $sMailExtName) = explode('.', $sMailTempName);
            $iMailId = $oSMailTemp->set(array(
                Const_MailTemp::F_NAME => $sMailTempName,
                Const_MailTemp::F_TEMP => $sMailTemp,
                Const_MailTemp::F_UTIME => time() ,
                Const_MailTemp::F_INUSE => 1,
                Const_MailTemp::F_WEBPOWERID => '2,96,958'
            ));
        }
        return true;
    }
    
}
