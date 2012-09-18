<?php

/**
 * Document: Vent
 * Created on: 2012-9-6, 15:05:47
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Task_Vent extends Task_Base {

    protected function main() {
        $this->vent();
    }

    protected function vent() {
        $sModClass=$this->sModClass;
        $oTask=$sModClass::getIns()->channel(0);
        for ($i = 0; $i < 10; $i++) {
            $oTask->msg($i);
        }
        $oTask->send();
    }
}