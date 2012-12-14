<?php
abstract class Util_SmsSender {
    public static function changty($aSms) {
        $aParams = array();
        $aParams['mobile'] = $aSms[Const_Sms::F_MOBILE];
        $aParams['content'] = rawurlencode(iconv('UTF-8', 'gb2312', $aSms[Const_Sms::F_CONTENT]));
        $aSmsService = json_decode(Store_Sms::getIns()->getService('changty') , true);
        $r = self::callApi($aSmsService[Const_Sms::C_SERVICE_URL], $aParams);
        $aResult = Util::xmlStringToArray($r);
        return ($aResult['Result'] == 1) ? true : false;
    }
    
    public static function montnets($aSms) {
        $aParams = array();
        $aParams['mobile'] = $aSms[Const_Sms::F_MOBILE];
        $aParams['count'] = count(explode(',', $aSms[Const_Sms::F_MOBILE]));
        $aParams['content'] = rawurlencode($aSms[Const_Sms::F_CONTENT]);
        $aSmsService = json_decode(Store_Sms::getIns()->getService('montnets') , true);
        $r = self::callApi($aSmsService[Const_Sms::C_SERVICE_URL], $aParams, 'post');
        $aResult = Util::xmlStringToArray($r);
        $iLength = strlen($aResult[0]);
        if ($iLength > 10 && $iLength < 25) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function emay($aSms) {
        $aParams = array();
        $aParams['mobile'] = $aSms[Const_Sms::F_MOBILE];
        $aParams['content'] = rawurlencode($aSms[Const_Sms::F_CONTENT]);
        $aSmsService = json_decode(Store_Sms::getIns()->getService('emay') , true);
        $r = self::callApi($aSmsService[Const_Sms::C_SERVICE_URL], $aParams);
        $aResult = Util::xmlStringToArray($r);
        return ($aResult['error'] == 0)?true:false;
    }
    
    public static function zxt($aSms) {
        $aParams = array();
        $aParams['mobile'] = $aSms[Const_Sms::F_MOBILE];
        $aParams['content'] = rawurlencode($aSms[Const_Sms::F_CONTENT]);
        $aSmsService = json_decode(Store_Sms::getIns()->getService('zxt') , true);
        $r = self::callApi($aSmsService[Const_Sms::C_SERVICE_URL], $aParams);
        return true;
    }
    
    protected static function callApi($sUrl, $aParams, $sMethod = 'get') {
        if (empty($sUrl)) {
            return false;
        }
        $aReplaceParams = array();
        foreach ($aParams as $k => $v) {
            $aReplaceParams['{$' . $k . '}'] = $v;
        }
        $sUrl = str_replace(array_keys($aReplaceParams) , array_values($aReplaceParams) , $sUrl);
        Util::output($sUrl);
        exit;
    }
}
