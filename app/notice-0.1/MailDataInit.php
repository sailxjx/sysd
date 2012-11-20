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
    
    protected function __construct() {
        parent::__construct();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
    }
    
    protected function main() {
        $this->initServices();
    }
    
    protected function initServices() {
        $sServiceKey = Redis_Key::mailServices();
        $oRedis = $this->oRedis;
        foreach ($this->aServices as $sService => $aService) {
            $oRedis->hset($sServiceKey, $sService, json_encode($aService));
        }
        return true;
    }
    
}
