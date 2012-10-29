<?php
abstract class Redis_SysKey {
    
    protected static $sPrefix = 'sys:';
    protected static $aMap = array(
        'sysproctable' => 'sys:proc:table:{$id}'
    );
    
    public static function __callStatic($name, $args) {
        $sName = strtolower($name);
        if (!isset(static ::$aMap[$sName])) {
            return static ::autoKey($name);
        }
        if (isset($args[0]) && is_array($args[0])) {
            extract($args[0]);
        }
        $sKey = static ::$sPrefix . static ::$aMap[$sName];
        @eval("\$sKey = \"$sKey\";");
        return $sKey;
    }
    
    protected static function autoKey($name) {
        return static ::$sPrefix . strtolower(preg_replace('/([a-z])([A-Z0-9])/', '$1:$2', $name));
    }
    
    // change real redis key to rediskey func
    public static function convKeyToFunc($sKey) {
        $aKey = explode(':', $sKey);
        unset($aKey[0]);
        $sKey = '';
        foreach ($aKey as $sVCEle) {
            $sKey.= ucfirst($sVCEle);
        }
        return $sKey;
    }
    
}
