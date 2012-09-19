<?php

/**
 * Document: Task_Base
 * Created on: 2012-9-11, 18:06:08
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Task_Base extends Base {

    protected $sMod;
    protected $sModPrefix='Mod_';
    protected $sModClass;

    protected function __construct(){
        parent::__construct();
        $this->sMod=isset($this->sMod)?$this->sMod:Util::getConfig('MOD_TASK');
        $this->sModClass=$this->sModPrefix.$this->sMod;
        if(!reqClass($this->sModClass)){
            trigger_error('could not find the req mod: '.$sMod, E_USER_ERROR);
        }
    }

}
