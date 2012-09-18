<?php

/**
 * Document: RTask
 * Created on: 2012-9-6, 16:43:00
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
class Mod_RTask extends Mod_Task {

    protected $oRedis;
    protected $aChannels=array(
        'mailTask' //msg key
        );
    protected $iUSec = 10000; //usleep time

    protected function __construct() {
        $this->oRedis = Fac_Db::getIns()->loadRedis();
    }

    protected function reset() {
        $this->aMsg = array();
        return $this;
    }

    public function recv() {
        $sMsgKey = $this->aChannels[$this->mChannel];
        while (!$sMsg = $this->oRedis->rpop(Redis_Key::$sMsgKey())) {
            usleep($this->iUSec);
        }
        return $sMsg;
    }

    public function send() {
        if (empty($this->aMsg)) {
            return false;
        }
        $this->oRedis->multi();
        $sMsgKey = $this->aChannels[$this->mChannel];
        foreach ($this->aMsg as $sMsg) {
            $this->oRedis->lpush(Redis_Key::$sMsgKey(), $sMsg);
        }
        $this->reset();
        return $this->oRedis->exec();
    }

}
