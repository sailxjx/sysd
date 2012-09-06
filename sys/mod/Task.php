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

    abstract public function send();

    abstract public function recv();

    abstract public function conf();

    public function msg($sMsg) {
        if($sMsg){
            $this->aMsg[] = $sMsg;
        }
        return $this;
    }

}
