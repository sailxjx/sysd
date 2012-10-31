<?php
class Server extends Base {
    
    protected $aDsn = array(
        'reply' => 'tcp://*:5555',
        'heartbeat' => 'tcp://*:5556'
    );
    
    protected function main() {
        $oPoll = new ZMQPoll();
        $oSockRep = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REP);
        $oSockRep->bind($this->aDsn['reply']);
        $oPoll->add($oSockRep, ZMQ::POLL_IN | ZMQ::POLL_OUT);
        $oSockHeartIn = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_SUB);
        $oSockHeartIn->connect($this->aDsn['heartbeat']);
        $oSockHeartIn->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, 'heartbeat');
        $oPoll->add($oSockHeartIn, ZMQ::POLL_IN);
        $aRead = $aWrite = array();
        Util::output('begin listening messages from: ' . implode(',', $this->aDsn));
        while (1) {
            try {
                $ie = $oPoll->poll($aRead, $aWrite);
                if ($ie > 0) {
                    foreach ($aRead as $oSock) {
                        if ($oSock === $oSockRep) {
                            $sMsg = $oSock->recv();
                            Util::output('get message: ' . $sMsg);
                            $sReply = $this->getReply($sMsg);
                            Util::output('reply: ' . $sReply);
                            $oSock->send($sReply);
                        } elseif ($oSock === $oSockHeartIn) {
                            Util::output('heartin: ' . $oSockHeartIn->recv());
                        }
                    }
                }
            }
            catch(ZMQPollException $e) {
                if ($e->getCode() === 4) {
                    pcntl_signal_dispatch();
                    continue;
                } else {
                    Util::output("poll failed: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * deal with messages and return the replies
     */
    protected function getReply($sMsg) {
        return Fac_SysMod::getIns()->loadModMsgDeal()->deal($sMsg);
    }
    
}
