<?php

class Mod_RLog extends Mod_Log {

    protected $oRedis;

    protected function __construct() {
        $this->oRedis = Fac_Db::getIns()->loadRedis();
    }

    protected function reset(){
        $this->aMsgs = array();
        return $this;
    }
    
    public function send(){
        if(empty($this->aMsgs)){
            return false;
        }
        $this->oRedis->multi();
        foreach ($this->aMsgs as $sMsg) {
            $this->oRedis->lpush();
        }
        $this->reset();
        return $this->oRedis->exec();
    }

}