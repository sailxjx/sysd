<?php

/**
 * Document: Mq
 * Created on: 2012-5-21, 11:31:01
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Fac_Mq {

    private static $oIns;
    protected $oZCon;

    /**
     * instance of Fac_Mq
     * @return Fac_Mq
     */
    public static function &getIns() {
        if (!isset(self::$oIns)) {
            self::$oIns = new Fac_Mq();
        }
        return self::$oIns;
    }

    /**
     * get zmq socket
     * @param string $iType
     * @return \ZMQSocket
     */
    public function loadZMQ($iType) {
        $oZCtxt = $this->getZCon();
        $oZSock = new ZMQSocket($oZCtxt, $iType);
        return $oZSock;
    }

    protected function getZCon(){
        if(!isset($this->oZCon)){
            $this->oZCon = new ZMQContext();
        }
        return $this->oZCon;
    }

}
