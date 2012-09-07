<?php

/**
 * Document: ZTask
 * Created on: 2012-9-6, 16:41:26
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
class Mod_ZTask extends Mod_Task {

    protected $oRecv;
    protected $oSend;
    protected $oReq;
    protected $oRep;
    protected $iCNum;//client number
    protected $iMinCNum=2;//min client number
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
            )
        );

    protected $aSync=array(
        'rep'=>array(
            'dsn'=>'ipc:///tmp/zmq_sync.ipc',
            'type'=>ZMQ::SOCKET_REP,
            'method'=>'bind'
            ),
        'req'=>array(
            'dsn'=>'ipc:///tmp/zmq_sync.ipc',
            'type'=>ZMQ::SOCKET_REQ,
            'method'=>'connect'
            )
        );

    protected function reset() {
        $this->aMsg = array();
        return $this;
    }

    /**
     * 
     * @return \ZMQSocket
     */
    protected function getRecv(){
        if(!isset($this->oRecv)){
            $this->oRecv=$this->getSocket($aConf=$this->aChannels[$this->iChannel]['recv']);
            $this->syncReq();
        }
        return $this->oRecv;
    }

    /**
     * 
     * @return \ZMQSocket
     */
    protected function getSend(){
        if(!isset($this->oSend)){
            $this->oSend=$this->getSocket($this->aChannels[$this->iChannel]['send']);
            $this->syncRep();
        }
        return $this->oSend;
    }

    public function recv() {
        $oRecv = $this->getRecv();
        if(!$sMsg=$oRecv->recv()){
            $this->syncReq();
        }
        return $sMsg;
    }

    public function send() {
        $oSend = $this->getSend();
        foreach ($this->aMsg as $sMsg) {
            $oSend->send($sMsg);
        }
        $this->reset();
        return true;
    }

    protected function syncReq(){
        if(!isset($this->oReq)){
            $this->oReq=$this->getSocket($this->aSync['req']);
        }
        return $this->oReq->send('one client connected')->recv();
    }

    protected function syncRep(){
        if(!isset($this->oRep)){
            $this->oRep=$this->getSocket($this->aSync['rep']);
        }
        while ($this->iCNum<$this->iMinCNum) {
            $this->oRep->recv();
            $this->oRep->send('');
            $this->iCNum++;
            Util::output($this->iCNum.' clients connected');
        }
        return $this;
    }

    protected function getSocket($aConf){
        $oSock=Fac_Mq::getIns()->loadZMQ($aConf['type']);
        $oSock->{$aConf['method']}($aConf['dsn']);
        return $oSock;
    }

}
