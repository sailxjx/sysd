<?php
class Store_Sms extends Store_Table{

    protected $aSyncFields = array(
        Const_Sms::F_ID,
        Const_Sms::F_MOBILE,
        Const_Sms::F_TYPE,
        Const_Sms::F_CTIME,
        Const_Sms::F_STATUS,
        Const_Sms::F_SERVICETYPE,
        Const_Sms::F_SMSTEMPLATE
    );
    protected $sSyncTable = 'notice_sms_table';

    public function getService($sName = null) {
        $sSvsKey = Redis_Key::smsServices();
        if (!isset($sName)) {
            return $this->oRedis->hgetall($sSvsKey);
        }
        return $this->oRedis->hget($sSvsKey, $sName);
    }

}