<?php
/**
 * Document: Base
 * Created on: 2012-6-4, 13:08:19
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Mod_SysBase {
    
    /**
     * instance
     * @var array
     */
    private static $aIns;
    
    /**
     * get a new instance
     * @return Mod_SysBase
     */
    public static function &getIns() {
        $sClass = get_called_class();
        if (!isset(self::$aIns[$sClass])) {
            self::$aIns[$sClass] = new $sClass();
        }
        return self::$aIns[$sClass];
    }
    
}
