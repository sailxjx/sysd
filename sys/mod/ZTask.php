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
    protected $aCSend = array(
        'dsn' => 'ipc:///tmp/zmq_send.ipc',
        'type' => ZMQ::SOCKET_PUSH
    );
    protected $aCRecv = array(
        'dsn' => 'ipc:///tmp/zmq_recv.ipc',
        'type' => ZMQ::SOCKET_PULL
    );

    /**
     *
     * @param type $aConf
     * @return \Mod_ZTask 
     */
    public function conf($aConf) {
        $this->aConf = $aConf;
        return $this;
    }

    protected function reset() {
        $this->aMsg = array();
        return $this;
    }

    /**
     * 
     * @return \ZMQSocket
     */
    protected function getRecv() {
        if (!isset($this->oRecv)) {
            $this->oRecv = Fac_Mq::getIns()->loadZMQ($this->aCRecv['type']);
            $this->oRecv->connect($this->aCRecv['type']);
        }
        return $this->oRecv;
    }

    /**
     *
     * @return \ZMQSocket
     */
    protected function getSend() {
        if (!isset($this->oSend)) {
            $this->oSend = Fac_Mq::getIns()->loadZMQ($this->aCSend['type']);
            $this->oSend->bind($this->aCSend['type']);
        }
        return $this->oSend;
    }

    public function recv() {
        $oRecv = $this->getRecv();
        return $oRecv->recv();
    }

    public function send() {
        $oSend = $this->getSend();
        $oSend->send(json_encode($this->aMsg));
        $this->reset();
        return true;
    }

}
