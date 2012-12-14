<?php
class Store_Sms extends Store_Table{
    public function getService($sName = null) {
        $sSvsKey = Redis_Key::smsServices();
        if (!isset($sName)) {
            return $this->oRedis->hgetall($sSvsKey);
        }
        return $this->oRedis->hget($sSvsKey, $sName);
    }    
}