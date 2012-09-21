<?php
/**
 * Document: Store_SysTable
 * Created on: 2012-8-22, 16:26:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Store_SysTable extends Mod_SysBase {
    
    protected $sTable;
    public static $aFields = array();
    protected $aData = array();
    protected $oRedis;
    protected $sRKeyClass = 'Redis_SysKey'; //class to build the redis key
    protected $sRExpClass = 'Redis_SysExpire'; //class to get the redis expire
    
    public function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->init();
    }
    
    /**
     * 初始化方法
     * @return \Store_SysTable
     */
    private function init() {
        $this->getTable();
        return $this;
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
    public function del() {
        return false;
    }
    
    public function get($iId, $mFields = null) {
        $oRedis = $this->oRedis;
        $sKey = $this->getTableKey($iId);
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
        $aData['id'] = $this->getId();
        $sRExpKey = strtoupper($this->sTable); //过期时间key
        $sKey = $this->getTableKey($aData['id']);
        $oRedis->hmset($sKey, $aData);
        $sRExpClass = $this->sRExpClass;
        if (isset($sRExpClass::$sRExpKey) && $sRExpClass::$sRExpKey > 0) { //只有在设置有效期常量时设置有效期
            $oRedis->expire($sKey, $sRExpClass::$sRExpKey);
        }
        $this->reset(); //重设fields供下次调用
        return $aData['id'];
    }
    
    protected function getTableKey($iId) {
        $sRTableFunc = $this->sTable . 'Table';
        $sRKeyClass = $this->sRKeyClass;
        return $sRKeyClass::$sRTableFunc(array(
            'id' => $iId
        ));
    }
    
    /**
     * 获取table名
     */
    protected function getTable() {
        if (!isset($this->sTable)) {
            list($sPre, $sTable) = explode('_', get_called_class());
            if (empty($sTable)) {
                trigger_error('could not find the called table', E_USER_ERROR);
            }
            $this->sTable = $sTable;
        }
        return $this->sTable;
    }
    
    public function __set($sKey, $sVal) {
        if (!isset(static ::$aFields[$sKey])) {
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
        $sRIdFunc = $this->sTable . 'Id'; //自增id key
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
