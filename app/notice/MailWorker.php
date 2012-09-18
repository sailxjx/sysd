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
        $oQMail = Queue_Mail::getIns();
        while($sMsg=$oWorker->recv()){
            echo 'pid: '.posix_getpid(),';';
            echo 'msg: '.$sMsg,PHP_EOL;
            if($this->doSth()){
                echo 'succ msg: '.$sMsg,PHP_EOL;
                $oQMail->succ($sMsg, time())->add();
            }else{
                echo 'error msg: '.$sMsg,PHP_EOL;
                $oQMail->error($sMsg, time())->add();
            }
            $oQMail->send($sMsg, 1)->rem();
        }
        return true;
    }

    protected function doSth(){
        sleep(1);
        return rand(0,1);
    }
}
