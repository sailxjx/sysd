<?php

abstract class Mod_Log extends Mod_Base {
    
    protected $aMsgs = array();
    abstract public function send();

    public function msg($sMsg) {
        if($sMsg){
            $this->aMsg[] = $sMsg;
        }
        return $this;
    }

}