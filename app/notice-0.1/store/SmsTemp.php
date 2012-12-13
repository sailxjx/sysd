<?php
class Store_SmsTemp extends Store_Table {
    protected $sPkField = Const_SmsTemp::F_NAME;
    public function set($aData = null) {
        $oRedis = $this->oRedis;
        if ($r = parent::set($aData)) {
            $sKey = $this->getTableKey($r);
            $aSmsTemp = $oRedis->hmget($sKey, array(
                Const_SmsTemp::F_NAME,
                Const_SmsTemp::F_INUSE
            ));
        }
        if ($aSmsTemp[Const_SmsTemp::F_INUSE] == 1 && !empty($aSmsTemp[Const_SmsTemp::F_NAME])) {
            $sSmsTempKey = Redis_Key::smsTemplates();
            $oRedis->zadd($sSmsTempKey, $aSmsTemp[Const_SmsTemp::F_INUSE], $aSmsTemp[Const_SmsTemp::F_NAME]);
        }
        return $r;
    }
    
    public function del($sPkVal) {
        if ($r = parent::del($sPkVal)) {
            $sSmsTempKey = Redis_Key::smsTemplates();
            $this->oRedis->zrem($sSmsTempKey, $sPkVal);
        }
        return $r;
    }
}
