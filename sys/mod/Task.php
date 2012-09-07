<?php

/**
 * Document: Task
 * Created on: 2012-9-6, 16:42:38
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Mod_Task extends Mod_Base {

    protected $aMsg = array();
    protected $aChannels=array();
    protected $iChannel=0;

    abstract public function send();

    abstract public function recv();

    public function channel($iCId=0) {
        if(!isset($this->aChannels[$iCId])){
            trigger_error('could not find this channel! ',E_USER_ERROR);
        }
        $this->iChannel=$iCId;
        return $this;
    }

    public function msg($sMsg) {
        if($sMsg){
            $this->aMsg[] = $sMsg;
        }
        return $this;
    }

}
