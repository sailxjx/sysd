<?php
class Server extends Base {
    
    protected $aDsn = array(
        'reply' => 'tcp://*:5555',
        'heartbeat' => 'tcp://*:5556'
    );
    
    protected $aPids = array();
    
    protected $aDaemons = array(
        'heartbeat',
        'serv'
    );
    
    protected function main() {
        $aOptions = $this->oCore->getOptions();
        if (!array_intersect(array(
            Const_SysCommon::OS_SLAVE,
            Const_SysCommon::OL_SLAVE
        ) , $aOptions)) { //master
            $this->goMaster();
        } else {
            $this->goSlave();
        }
    }
    
    protected function goMaster() {
        foreach ($this->aDaemons as $sFunc) {
            $iPid = pcntl_fork();
            if ($iPid === - 1) {
                Util::output('could not fork: ' . $sFunc);
                exit;
            } elseif ($iPid) { //parent
                $this->aPids[$sFunc] = $iPid;
                continue;
            } else { //child
                $iPid = posix_getpid();
                Util_SysUtil::addPid($iPid);
                Daemonize::getIns()->logRunData();
                $this->{$sFunc}();
                return true;
            }
            //parent
        }
        while (1) {
            if($iCPid = pcntl_wait($iStatus)){

            }
        }
    }
    
    protected function goSlave() {
        
    }
    
    protected function heartbeat() {
        
    }
    
    protected function serv() {
        $oPoll = new ZMQPoll();
        $oSockRep = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REP);
        $oSockRep->bind($this->aDsn['reply']);
        $oPoll->add($oSockRep, ZMQ::POLL_IN | ZMQ::POLL_OUT);
        $oSockHeart = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REP);
        $oSockHeart->bind($this->aDsn['heartbeat']);
        $oPoll->add($oSockHeart, ZMQ::POLL_IN | ZMQ::POLL_OUT);
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
                        } elseif ($oSock === $oSockHeart) {
                            Util::output($oSock->recv());
                            $oSock->send('heartbeat: i heard about you!');
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
    
    protected function waitForMaster() {
        $oHeartReq = new ZMQSocket(new ZMQContext() , ZMQ::SOCKET_REQ);
    }
    
    /**
     * deal with messages and return the replies
     * @return string
     */
    protected function getReply($sMsg) {
        return Fac_SysMod::getIns()->loadModMsgDeal()->deal($sMsg);
    }
    
}
