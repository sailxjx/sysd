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
        $oWorker = $this->oTask;
        $oQMail = Queue_Mail::getIns();
        while ($sMsg = $oWorker->channel(Const_Task::C_MAILLIST)->recv()) {
            Util::output('recv msg: ' . $sMsg);
            if ($this->doSth()) {
                Util::output('succ msg: ' . $sMsg);
                $oWorker->channel(Const_Task::C_MAILRESULT)->msg($sMsg . $this->sSplite . 'succ')->send();
            } else {
                Util::output('error msg: ' . $sMsg);
                $oWorker->channel(Const_Task::C_MAILRESULT)->msg($sMsg . $this->sSplite . 'error')->send();
            }
        }
        return true;
    }
    
    protected function doSth() {
        sleep(1);
        return rand(0, 1);
    }
}
