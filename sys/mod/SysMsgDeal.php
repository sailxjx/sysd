<?php
class Mod_SysMsgDeal extends Mod_SysBase {
    public function deal($sMsg) {
        $aMsg = json_decode($sMsg, true);
        $sFunc = $aMsg['func'];
        $aParams = $aMsg['params'];
        if (method_exists($this, $sFunc)) {
            return json_encode(call_user_func(array(
                $this,
                $sFunc
            ) , $aParams));
        }
        return false;
    }
    
    protected function getConfigs() {
        return Util::getConfig();
    }
}
