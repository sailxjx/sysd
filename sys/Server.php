<?php
class Server extends Base {
    
    protected $aDsn = array(
        'tcp://*:5555',
        'tcp://*:5556'
    );
    
    protected $aSocks;
    protected $oPoll;
    
    protected function main() {
        $oPoll = $this->getPoll();
        $aRead = $aWrite = array();
        Util::output('begin listening messages from: ' . implode(',', $this->aDsn));
        while (1) {
            try {
                $ie = $oPoll->poll($aRead, $aWrite);
                if ($ie > 0) {
                    foreach ($aRead as $oSock) {
                        $sMsg = $oSock->recv();
                        Util::output('get message: ' . $sMsg);
                        if ($this->getReply($sMsg)) {
                            $oSock->send($this->getReply($sMsg));
                        } else {
                            $oSock->send($this->errReturn($sMsg));
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
    
    protected function getSocks() {
        if (!isset($this->aSocks)) {
            foreach ($this->aDsn as $sDsn) {
                $oSock = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REP);
                $oSock->bind($sDsn);
                $this->aSocks[] = $oSock;
            }
        }
        return $this->aSocks;
    }
    
    protected function getPoll() {
        if (!isset($this->oPoll)) {
            $aSocks = $this->getSocks();
            $this->oPoll = new ZMQPoll();
            foreach ($aSocks as $oSock) {
                $this->oPoll->add($oSock, ZMQ::POLL_IN | ZMQ::POLL_OUT);
            }
        }
        return $this->oPoll;
    }
    
    /**
     * deal with messages and return the replies
     */
    protected function getReply($sMsg) {
        return Mod_SysMsgDeal::getIns()->deal($sMsg);
    }
    
    protected function errReturn($sMsg) {
        return json_encode(array(
            'status' => 'error',
            'msg' => $sMsg
        ));
    }
    
}
