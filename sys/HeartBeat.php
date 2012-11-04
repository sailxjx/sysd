<?php
class HeartBeat extends Base {
    
    protected $sDsn = 'tcp://127.0.0.1:5556';
    protected function main() {
        $oSocket = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REQ);
        $oSocket->connect($this->sDsn);
        Util::output('begin to request to ' . $this->sDsn);
        while (1) {
            Util::output($oSocket->send('heartbeat: i am alive!')->recv());
            sleep(5);
        }
    }
}
