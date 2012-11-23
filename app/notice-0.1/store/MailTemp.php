<?php
class Store_MailTemp extends Store_Table {
    
    protected $sPkField = Const_MailTemp::F_NAME;
    public function set($aData = null) {
        $oRedis = $this->oRedis;
        if ($r = parent::set($aData)) {
            $sKey = $this->getTableKey($r);
            $aMailTemp = $oRedis->hmget($sKey, array(
                Const_MailTemp::F_NAME,
                Const_MailTemp::F_INUSE
            ));
            if($aMailTemp[Const_MailTemp::F_INUSE] == 1 && !empty($aMailTemp[Const_MailTemp::F_NAME])){
                $sMailTempKey = Redis_Key::mailTemplates();
                $oRedis->zadd($sMailTempKey, $aMailTemp[Const_MailTemp::F_INUSE], $aMailTemp[Const_MailTemp::F_NAME]);
            }
        }
        return $r;
    }

    public function del($sPkVal){
        if($r = parent::del($sPkVal)) {
            $sMailTempKey = Redis_Key::mailTemplates();
            $this->oRedis->zrem($sMailTempKey, $sPkVal);
        }
        return $r;
    }
}
