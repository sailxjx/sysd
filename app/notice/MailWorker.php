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
        $oWorker=Mod_RTask::getIns();
        while($sMsg=$oWorker->recv()){
            echo $sMsg,PHP_EOL;
            sleep(1);
        }
        return true;
    }

}
