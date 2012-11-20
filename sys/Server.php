<?php
class Server extends Base {
    
    protected $aDsn = array(
        'reply' => 'tcp://*:5555'
    );
    
    protected $aPids = array();
    protected $aDaemons = array(
        'serv',
        'listen'
    );
    
    /**
     * for now, there is only master
     *
     */
    protected function main() {
        $this->goMaster();
    }
    
    protected function goMaster() {
        foreach ($this->aDaemons as $sFunc) {
            $iPid = pcntl_fork();
            if ($iPid === - 1) {
                Util::output('could not fork: ' . $sFunc);
                exit;
            } elseif ($iPid) { //parent
                $this->aPids[$iPid] = $sFunc;
                continue;
            } else { //child
                $iPid = posix_getpid();
                Util_SysUtil::addPid($iPid);
                return $this->{$sFunc}();
            }
        }
        $this->waitForChild();
        return true;
    }
    
    protected function waitForChild() {
        while (1) {
            if ($iCPid = pcntl_wait($iStatus)) {
                Util::output("job {$iCPid} has exited!");
                sleep(1); //wait for signal handle
                $sFunc = $this->aPids[$iCPid];
                if (method_exists($this, $sFunc)) {
                    $iPid = pcntl_fork();
                    if ($iPid == - 1) {
                        Util::output('could not fork: ' . $sFunc);
                        exit;
                    } elseif ($iPid) {
                        $this->aPids[$iPid] = $sFunc;
                    } else {
                        $iPid = posix_getpid();
                        Util_SysUtil::addPid($iPid);
                        return $this->{$sFunc}();
                    }
                }
            }
        }
        return true;
    }
    
    protected function goSlave() {
        
    }

    protected function listen() {
        Listener::getIns()->run();
        return true;
    }
    
    protected function serv() {
        $oPoll = new ZMQPoll();
        $oSockRep = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REP);
        $oSockRep->bind($this->aDsn['reply']);
        $oPoll->add($oSockRep, ZMQ::POLL_IN | ZMQ::POLL_OUT);
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
                            $sReply = is_scalar($sReply) ? $sReply : json_encode($sReply);
                            Util::output('reply: ' . $sReply);
                            $oSock->send($sReply);
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
     * @return string
     */
    protected function getReply($sMsg) {
        return Fac_SysMod::getIns()->loadModMsgDeal()->deal($sMsg);
    }
    
}
