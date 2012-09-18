<?php

/**
 * Document: MailWorker
 * Created on: 2012-9-6, 15:01:18
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
class MailWorker extends Task_Worker {

    protected function main() {
        $this->work();
    }
    protected function work(){
        $sModClass=$this->sModClass;
        $oWorker=$sModClass::getIns()->channel(0);
        while($sMsg=$oWorker->recv()){
            echo 'pid: '.posix_getpid(),';';
            echo 'msg: '.$sMsg,PHP_EOL;
            echo $i++;
        }
        return true;
    }
}
