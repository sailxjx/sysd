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
    protected $oSub;
    protected $oPub;
    protected $aPoll;
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
        }
        return $this->oSend;
    }

    protected function getSub(){
        if(!isset($this->oSub)){
            $this->oSub=$this->getSocket($this->aSync['sub']);
        }
        return $this->oSub;
    }

    protected function getPub(){
        if(!isset($this->oPub)){
            $this->oPub=$this->getSocket($this->aSync['pub']);
        }
        return $this->oPub;
    }

    protected function getPoll($sType,$aPollIn=array()){
        if(!isset($this->aPoll[$sType])){
            $this->aPoll[$sType]=new ZMQPoll();
            foreach ($aPollIn as $oSock) {
                $this->aPoll[$sType]->add($oSock, ZMQ::POLL_IN);
            }
        }
        return $this->aPoll[$sType];
    }

    public function recv() {
        $oRecv = $this->getRecv();
        $oSub = $this->getSub();
        $oSub->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "MAIL");
        $oPoll = new ZMQPoll();
        $oPoll->add($oRecv, ZMQ::POLL_IN);
        $oPoll->add($oSub, ZMQ::POLL_IN);
        $aRead = array();
        $aWrite = array();
        while(1){
            $iEve = $oPoll->poll($aRead, $aWrite);
            var_dump($iEve);
            if($iEve > 0){
                foreach ($aRead as $oSock) {
                    if($oSock == $oRecv && $sMsg = $oSock->recv()){
                        return $sMsg;
                    }
                }
            }
            sleep(1);
        }
        return false;
    }

    public function send() {
        $oSend = $this->getSend();
        $oPub=$this->getPub();
        
        $iNum=0;
        while($iNum<2){
            $oPub->send('');
            Util::output('1 client connected');
            $iNum++;
        }
        exit;
        $iNum = 0;
        while ( $iNum < $this->iMinCNum ) {
            $oSub->recv();
            $iNum++;
            Util::output($iNum.' clients connected');
        }
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
        return $this->oReq;
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

    public function tstSend(){
        $oSend=Fac_Mq::getIns()->loadZMQ(ZMQ::SOCKET_REQ);
        $oSend->bind('ipc:///tmp/test.ipc');

        // $oSend1=Fac_Mq::getIns()->loadZMQ(ZMQ::SOCKET_REQ);
        // $oSend1->connect('ipc:///tmp/test1.ipc');

        $oPoll=new ZMQPoll();
        $oPoll->add($oSend, POLL_OUT);
        // $oPoll->add($oSend1, POLL_OUT);
        $aWrite=array();
        $aRead=array();
        while(1){
            $iEve=$oPoll->poll($aRead,$aWrite);
            if($iEve>0){
                foreach ($aWrite as $oSock) {
                    echo $oSock->send("req\n")->recv();
                }
                // foreach ($aRead as $oSock) {
                //     echo $oSock->recv();
                // }
            }
            sleep(1);
        }
        exit;
    }

    public function tstRecv(){
        $oRecv=Fac_Mq::getIns()->loadZMQ(ZMQ::SOCKET_REP);
        $oRecv->connect('ipc:///tmp/test.ipc');

        $oRecv1=Fac_Mq::getIns()->loadZMQ(ZMQ::SOCKET_SUB);
        $oRecv1->connect('ipc:///tmp/test1.ipc');
        $oRecv1->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, '');

        $oPoll=new ZMQPoll();
        $oPoll->add($oRecv, POLL_IN);
        $oPoll->add($oRecv1, POLL_IN);
        $aWrite=array();
        $aRead=array();

        while(1){
            $iEve=$oPoll->poll($aRead,$aWrite);
            if($iEve){
                foreach ($aRead as $oSock) {
                    if($oSock==$oRecv){
                        echo "rep:",$oSock->recv();
                        $oSock->send("rep\n");
                    }elseif($oSock==$oRecv1){
                        echo "sub:",$oSock->recv();
                    }
                }
            }
        }
        exit;
    }

}
