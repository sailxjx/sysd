<?php
/**
 * Document: MailWorker
 * Created on: 2012-9-6, 15:01:18
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailWorker extends Task_Worker {
    
    protected function main() {
        $this->work();
    }
    
    protected function work() {
        $sModClass = $this->sModClass;
        $oWorker = $sModClass::getIns()->channel(0);
        $oQMail = Queue_Mail::getIns();
        while ($sMsg = $oWorker->recv()) {
            Util::output('pid: ' . posix_getpid() . ';');
            Util::output('msg: ' . $sMsg);
            if ($this->doSth()) {
                Util::output('succ msg: ' . $sMsg);
                $oQMail->move('send', 'succ', $sMsg, time());
            } else {
                Util::output('error msg: ' . $sMsg);
                $oQMail->move('send', 'error', $sMsg, time());
            }
        }
        return true;
    }
    
    protected function doSth() {
        sleep(1);
        return rand(0, 1);
    }
}
