<?php

/**
 * Document: Worker
 * Created on: 2012-9-6, 15:06:08
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Task_Worker extends Task_Base {

    protected function main() {
        $this->work();
    }

    protected function work(){
        $sModClass=$this->sModClass;
        $oWorker=$sModClass::getIns()->channel(0);
        while($sMsg=$oWorker->recv()){
            echo 'pid: '.posix_getpid(),';';
            echo 'msg: '.$sMsg,PHP_EOL;
        }
        return true;
    }

}
