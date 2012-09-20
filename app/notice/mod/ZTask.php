<?php

/**
 * Document: ZTask
 * Created on: 2012-9-6, 16:41:26
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
class Mod_ZTask extends Mod_Task {

    protected $aPoll = array();//poll pool
    protected $aSock = array();//sock pool
    protected $iCNum;//client number
    protected $iMinWorkerNum = 2;//min client number
    protected $aWorkerPids = array();
    protected $aChannels = array(
        array(
            'send'=>array(
                'dsn'=>'ipc:///tmp/zmq_sock0.ipc',
                'type'=>ZMQ::SOCKET_PUSH,
                'method'=>'bind'
                ),
            'recv'=>array(
                'dsn'=>'ipc:///tmp/zmq_sock0.ipc',
                'type'=>ZMQ::SOCKET_PULL,
                'method'=>'connect'
                )
            ),
        'sync'=>array(
            'pub'=>array(
                'dsn'=>'ipc:///tmp/zmq_sync.ipc',
                'type'=>ZMQ::SOCKET_PUB,
                'method'=>'connect'
                ),
            'sub'=>array(
                'dsn'=>'ipc:///tmp/zmq_sync.ipc',
                'type'=>ZMQ::SOCKET_SUB,
                'method'=>'bind'
                )
            )
        );

    protected function reset() {
        $this->aMsg = array();
        return $this;
    }

    protected function getSock($sType, $mChannel){
        $sKey=md5(serialize(func_get_args()));
        if(!isset($this->aSock[$sKey])){
            $this->aSock[$sKey] = $this->loadSock($this->aChannels[$mChannel][$sType]);
        }
        return $this->aSock[$sKey];
    }

    protected function getPoll($sAct, $aSocks){
        if(empty($sAct)){
            return false;
        }
        if(!isset($this->aPoll[$sAct])){
            $oPoll = new ZMQPoll();
            foreach ($aSocks as $aSock) {
                $oPoll->add($aSock['s'], $aSock['t']);
            }
            $this->aPoll[$sAct] = $oPoll;
        }
        return $this->aPoll[$sAct];
    }

    public function recv() {
        $oRecv = $this->getSock('recv', $this->mChannel);
        $oPub = $this->getSock('pub', 'sync');
        $aSocks = array(
            array(
                's'=>$oRecv,
                't'=>ZMQ::POLL_IN
                ),
            array(
                's'=>$oPub,
                't'=>ZMQ::POLL_OUT
                )
            );
        $oPoll = $this->getPoll('recv', $aSocks);
        $aRead = $aWrite = array();
        while(1){
            $iEve = $oPoll->poll($aRead, $aWrite);
            if($iEve > 0){
                foreach ($aRead as $oSock) {
                    if($oSock == $oRecv && $sMsg = $oSock->recv()){
                        return $sMsg;
                    }
                }
                foreach ($aWrite as $oSock) {
                    if($oSock == $oPub){
                        $oSock->send(posix_getpid());
                        sleep(1);
                    }
                }
            }
        }
        return false;
    }

    public function send() {
        $oSend = $this->getSock('send', $this->mChannel);
        $this->sync();
        Util::output('sending');
        foreach ($this->aMsg as $sMsg) {
            $oSend->send($sMsg);
        }
        $this->reset();
        return true;
    }

    /**
     * sync for server and clients
     */
    protected function sync(){
        $oSub = $this->getSock('sub', 'sync');
        $oSub->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, '');
        $iWNum = 0;
        Util::output('wait for workers connected');
        while($iWNum < $this->iMinWorkerNum){
            $sMsg = $oSub->recv();
            $this->aWorkerPids[$sMsg]='';
            $iWNum = count($this->aWorkerPids);
        }
        Util::output($iWNum.' workers connected');
        return $this;
    }

    protected function loadSock($aConf){
        $oSock=Fac_SysMq::getIns()->loadZMQ($aConf['type']);
        $oSock->{$aConf['method']}($aConf['dsn']);
        return $oSock;
    }

}
