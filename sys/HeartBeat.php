<?php
class HeartBeat extends Base {
    
    protected $sDsn = 'tcp://*:5556';
    protected function main() {
        $oSocket = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_PUB);
        $oSocket->bind($this->sDsn);
        Util::output('begin to publish to ' . $this->sDsn);
        while(1){
            $oSocket->send('heartbeat: i am alive!');
            sleep(1);
        }
    }
}
