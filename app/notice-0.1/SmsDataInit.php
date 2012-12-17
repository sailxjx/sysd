<?php
class SmsDataInit extends Base {
    
    protected $aTemp = array(
        'smstest' => '{$username}，短信测试'
    );
    protected $aService = array(
        'changty' => array(
            'score' => 1,
            'desc' => '畅天游',
            'url' => 'http://si.800617.com:4400/SendSmsSr.aspx?un=shzyxx-1&pwd=d1ef7e&mobile={$mobile}&msg={$content}',
            'pool' => 'high'
        ) ,
        'montnets' => array(
            'score' => 2,
            'desc' => '梦网',
            'url' => 'http://61.145.229.29:7902/MWGate/wmgw.asmx/MongateCsSpSendSmsNew?userId=J20220&password=886513&pszMobis={$mobile}&pszMsg={$content}%20&iMobiCount={$count}&pszSubPort=10690%20333%2051100',
            'pool' => 'low'
        ) ,
        'emay' => array(
            'score' => 3,
            'desc' => '亿美',
            'url' => 'http://sdkhttp.eucp.b2m.cn/sdkproxy/sendsms.action?cdkey=3SDK-EMS-0130-MBVOK&password=898705&phone={$mobile}&message={$content}',
            'pool' => 'high'
        ) ,
        'zxt' => array(
            'score' => 4,
            'desc' => '资信通',
            'url' => 'http://218.241.67.233:9002/QxtSms/QxtFirewall?OperID=51fanli&OperPass=23397282&SendTime=&ValidTime=&AppendID=&DesMobile={$mobile}&Content={$content}&ContentType=8',
            'pool' => 'low'
        ) ,
        'baiwu' => array(
            'score' => 5,
            'desc' => '百悟',
            'url' => 'http://123.196.114.68:8080/sms_send2.do?corp_id=1cbq002&corp_pwd=yhsaqx&corp_service=10657516yd&mobile={$mobile}&msg_content={$content}',
            'pool' => 'low'
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
