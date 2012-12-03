<?php
/**
 * Document: Store_SysTable
 * Created on: 2012-8-22, 16:26:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Store_SysTable extends Mod_SysBase {
    
    protected $aFields;
    protected $iExpire;
    protected $sPkField;
    protected $aData = array();
    protected $oRedis;
    protected $sRKeyClass = 'Redis_SysKey'; //class to build the redis key
    
    public function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->init();
    }
    
    /**
     * 初始化方法
     * @return \Store_SysTable
     */
    private function init() {
        return $this;
    }
    
    public function getFields() {
        if (!isset($this->aFields)) {
            $sTable = self::getTable();
            $aFields = array();
            if (reqClass("Const_{$sTable}")) {
                $oRef = new ReflectionClass("Const_{$sTable}");
                $aFieldCons = $oRef->getConstants();
                foreach ($aFieldCons as $sKey => $sField) {
                    if (strpos($sKey, 'F_') === 0) {
                        $aFields[$sField] = $sField;
                    }
                }
            }
            $this->aFields = $aFields;
        }
        return $this->aFields;
    }

    public function getExpire() {
        if(!isset($this->iExpire)) {
            $sTable = self::getTable();
            $sConstTable = "Const_{$sTable}";
            $iExpire = -1;
            if (reqClass($sConstTable) && method_exists($sConstTable, "getStoreExpire")) {
                $iExpire = call_user_func("{$sConstTable}::getStoreExpire");
            }
            $this->iExpire = $iExpire;
        }
        return $this->iExpire;
    }
    
    /**
     * 重置成员变量
     * @return \Store_SysTable
     */
    public function reset() {
        $this->aData = array();
        return $this;
    }
    
    /**
     * can not del data now
     */
    public function del($sPkVal) {
        $oRedis = $this->oRedis;
        $sKey = $this->getTableKey($sPkVal);
        $r = $oRedis->del($sKey);
        $this->reset();
        return $r;
    }
    
    public function get($sPkVal, $mFields = null) {
        $oRedis = $this->oRedis;
        $sKey = $this->getTableKey($sPkVal);
        if (empty($mFields)) {
            return $oRedis->hgetall($sKey);
        }
        if (is_string($mFields)) {
            $aData = $oRedis->hmget($sKey, array(
                $mFields
            ));
            return isset($aData[$mFields]) ? $aData[$mFields] : null;
        } else {
            return $oRedis->hmget($sKey, $mFields);
        }
    }
    
    /**
     * 包装data
     * @param type $aData
     */
    public function set($aData = null) {
        foreach ((array)$aData as $sKey => $sVal) {
            $this->$sKey = $sVal;
        }
        $aData = $this->aData;
        if (empty($aData)) {
            return false;
        }
        $oRedis = $this->oRedis;
        $sPkField = $this->getPkField();
        $aData[$sPkField] = $this->getPkVal();
        if(empty($aData[$sPkField])){
            return false;
        }
        $sRExpKey = strtoupper(self::getTable()); //过期时间key
        $sKey = $this->getTableKey($aData[$sPkField]);
        $oRedis->hmset($sKey, $aData);
        if (($iExpire = $this->getExpire()) > 0) { //只有在设置有效期常量时设置有效期
            $oRedis->expire($sKey, $iExpire);
        }
        $this->reset(); //重设fields供下次调用
        return $aData[$sPkField];
    }
    
    protected function getPkField() {
        return isset($this->sPkField) ? $this->sPkField : 'id';
    }

    protected function getPkVal() {
        $sPkField = $this->getPkField();
        if($sPkField == 'id'){
            return $this->getId();
        }else{
            if (isset($this->aData[$sPkField])) {
                return $this->aData[$sPkField];
            }else{
                trigger_error("pk [{$sPkField}] value not exist!", E_USER_WARNING);
                return null;
            }
        }
    }
    
    protected function getTableKey($sPkVal) {
        $sRTableFunc = self::getTable() . 'Table';
        $sRKeyClass = $this->sRKeyClass;
        return $sRKeyClass::$sRTableFunc(array(
            'id' => $sPkVal
        ));
    }
    
    /**
     * 获取table名
     */
    public static function getTable() {
        list($sPre, $sTable) = explode('_', get_called_class());
        if (empty($sTable)) {
            trigger_error('could not find the called table', E_USER_ERROR);
        }
        return $sTable;
    }
    
    public function __set($sKey, $sVal) {
        $aFields = $this->getFields();
        if (!isset($aFields[$sKey])) {
            trigger_error('set an illegal key in table fields [' . $sKey . ']', E_USER_WARNING);
            return false;
        }
        $this->aData[$sKey] = $sVal;
        return true;
    }
    
    /**
     * 获取更新id并修复自增id为最大id
     * @return int
     */
    protected function getId() {
        $oRedis = $this->oRedis;
        $sRIdFunc = self::getTable() . 'Id'; //自增id key
        $sRKeyClass = $this->sRKeyClass;
        $sIdKey = $sRKeyClass::$sRIdFunc();
        $iAutoIncrId = $oRedis->get($sIdKey);
        if (isset($this->aData['id'])) { //设置id
            if (intval($this->aData['id']) > intval($iAutoIncrId)) { //设置id大于自增id
                trigger_error('the setting id is larger than auto_incr id', E_USER_WARNING);
                $oRedis->set($sIdKey, $this->aData['id']);
            }
        } else { //自增id
            $this->aData['id'] = $oRedis->incr($sIdKey);
        }
        return $this->aData['id'];
    }
    
}
