<?php

/**
 * Document: Store_Table
 * Created on: 2012-8-22, 16:26:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Store_Table extends Model_Base {

    protected $sTable;
    protected $aMap;
    protected $aFields = array();
    protected $oRedis;

    public function __construct() {
        $this->oRedis = Fac_Db::getIns()->loadRedis();
        $this->init();
    }

    /**
     * 初始化方法
     * @return \Store_Table 
     */
    private function init() {
        $sTable = $this->getTable();
        $sFArr = 'a' . ucfirst($sTable);
        if (!isset(Store_Fields::$$sFArr)) {
            trigger_error('could not find the called fields', E_USER_ERROR);
        }
        $this->aMap = Store_Fields::$$sFArr;
        return $this;
    }

    /**
     * 重置成员变量
     * @return \Store_Table 
     */
    public function reset() {
        $this->aFields = array();
        return $this;
    }

    public function del() {
        
    }

    public function get($iId, $mFields = null) {
        $oRedis = $this->oRedis;
        $sKey = $this->getTableKey($iId);
        if (empty($mFields)) {
            return $oRedis->hgetall($sKey);
        }
        if (is_string($mFields)) {
            $aData = $oRedis->hmget($sKey, array($mFields));
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
        foreach ((array) $aData as $sKey => $sVal) {
            $this->$sKey = $sVal;
        }
        $aFields = $this->aFields;
        if (empty($aFields)) {
            return false;
        }
        $oRedis = $this->oRedis;
        $aFields['id'] = $this->getId();
        $sRExpKey = strtoupper($this->sTable); //过期时间key
        $sKey = $this->getTableKey($aFields['id']);
        $oRedis->hmset($sKey, $aFields);
        if (isset(Redis_Expire::$sRExpKey) && Redis_Expire::$sRExpKey > 0) {//只有在设置有效期常量时设置有效期
            $oRedis->expire($sKey, Redis_Expire::$sRExpKey);
        }
        $this->reset(); //重设fields供下次调用
        return $aFields['id'];
    }

    protected function getTableKey($iId) {
        $sRTableFunc = $this->sTable . 'Table';
        return Redis_Key::$sRTableFunc(array('id' => $iId));
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
        if (!isset($this->aMap[$sKey])) {
            trigger_error('set an illegal key in table fields', E_USER_WARNING);
            return false;
        }
        $this->aFields[$sKey] = $sVal;
        return true;
    }

    /**
     * 获取更新id并修复自增id为最大id
     * @return int
     */
    protected function getId() {
        $oRedis = $this->oRedis;
        $sRIdFunc = $this->sTable . 'Id'; //自增id key
        $sIdKey = Redis_Key::$sRIdFunc();
        if (isset($this->aFields['id'])) {//设置id
            if (intval($this->aFields['id']) > $oRedis->get($sIdKey)) {//设置id大于自增id
                trigger_error('the setting id is larger than auto_incr id', E_USER_WARNING);
                $oRedis->set($sIdKey, $this->aFields['id']);
            }
        } else {//自增id
            $this->aFields['id'] = $oRedis->incr($sIdKey);
        }
        return $this->aFields['id'];
    }

}
