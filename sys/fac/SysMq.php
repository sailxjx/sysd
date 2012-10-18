<?php

/**
 * Document: Mq
 * Created on: 2012-5-21, 11:31:01
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Fac_SysMq {

    private static $oIns;
    protected $oZCon;

    /**
     * instance of Fac_SysMq
     * @return Fac_SysMq
     */
    public static function &getIns() {
        if (!isset(self::$oIns)) {
            self::$oIns = new Fac_SysMq();
        }
        return self::$oIns;
    }

    /**
     * get zmq socket
     * @param string $iType
     * @return \ZMQSocket
     */
    public function loadZMQ($iType) {
        return new ZMQSocket($this->getZCon(), $iType);
    }

    protected function getZCon(){
        if(!isset($this->oZCon)){
            $this->oZCon = new ZMQContext();
        }
        return $this->oZCon;
    }

}
