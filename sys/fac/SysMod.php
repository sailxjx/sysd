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
    
}
