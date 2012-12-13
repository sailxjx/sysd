<?php
class SmsDataInit extends Base {
    
    protected $aTemp = array(
        'smstest' => '{$username}，短信测试'
    );
    protected $aService = array(
        'changty' => array(
            'score' => 1,
            'desc' => '畅天游',
            'pool' => 'high'
        ) ,
        'montnets' => array(
            'score' => 2,
            'desc' => '梦网',
            'pool' => 'low'
        ) ,
        'emay' => array(
            'score' => 3,
            'desc' => '亿美',
            'pool' => 'high'
        )
    );
    protected $oRedis;
    
    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->setTemp();
        $this->setService();
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
    
    protected function setService() {
        $sSvsKey = Redis_Key::smsServices();
        $oRedis = $this->oRedis;
        foreach ($this->aService as $sService => $aService) {
            $oRedis->hset($sSvsKey, $sService, json_encode($aService));
        }
        return true;
    }
}
