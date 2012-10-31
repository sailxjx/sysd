<?php
class Fac_SysMod {
    protected static $oIns;
    protected function __construct() {
        
    }
    
    /**
     * instance of factory
     * @return Fac_SysDb
     */
    public static function &getIns() {
        if (!isset(self::$oIns)) {
            $sClass=get_called_class();
            self::$oIns = new $sClass();
        }
        return self::$oIns;
    }

    /**
     * load mod msg deal
     * @return Mod_MsgDeal
     */
    public function loadModMsgDeal(){
        if(!isset($this->oModMsgDeal)){
            $sModMsgDeal = Util::getConfig('MOD_MSGDEAL');
            if (reqClass($sModMsgDeal)) {
                $this->oModMsgDeal = $sModMsgDeal::getIns();
            }else{
                $this->oModMsgDeal = Mod_SysMsgDeal::getIns();
            }
        }
        return $this->oModMsgDeal;
    }
    
}
