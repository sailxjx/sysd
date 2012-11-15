<?php
class HeartBeat extends Base {
    
    protected $sDsn = 'tcp://127.0.0.1:5556';
    protected function main() {
        $oSockHeartIn = new ZMQSocket(new ZMQContext() , ZMQ::SOCKET_SUB);
        $oSockHeartIn->connect($this->sDsn);
        $oSockHeartIn->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, 'heartbeat');
        while (1) {
            try {
                $sMsg = $oSockHeartIn->recv();
                Util::output('get msg: ' . $sMsg);
            }
            catch(ZMQSocketException $e) {
                if ($e->getCode() === 4) {
                    pcntl_signal_dispatch();
                    continue;
                } else {
                    Util::output("sub failed: " . $e->getMessage());
                }
            }
        }
        return true;
    }
}
