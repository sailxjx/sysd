<?php
class SmsDataInit extends Base {
    
    protected $aTemp = array(
        'smstest' => '{$username}，短信测试'
    );
    protected $oRedis;
    
    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->setTemp();
    }
    
    protected function setTemp() {
        $oSSmsTemp = Store_SmsTemp::getIns();
        foreach ($this->aTemp as $k => $v) {
            $oSSmsTemp->set(array(
                Const_SmsTemp::F_NAME => $k,
                Const_SmsTemp::F_TEMP => $v,
                Const_SmsTemp::F_UTIME => time() ,
                Const_SmsTemp::F_INUSE => 1
            ));
        }
    }
}