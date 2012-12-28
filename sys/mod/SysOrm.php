<?php
class Mod_SysOrm extends Mod_SysBase {
    
    protected $aPdos;
    protected $sTable = 'notice_mail_table';
    protected $aTableDesc;
    protected $sDbSlave = 'MYSQL';
    protected $sDbMaster = 'MYSQL';
    protected $sDbType = 'mysql';
    protected $aFields;
    protected $aOpts = array('=', '>', '<', '>=', '<=', '<>', '!=', 'IN', 'LIKE');
    protected $aFilters = array();
    protected $aParams;
    /**
     * db slave
     */
    protected $oDbSlave;
    /**
     * db master
     */
    protected $oDbMaster;
    /**
     * use redis for cache
     */
    protected $oRedis;
    const KEY_TABLE_FIELDSET = 'orm:table:fieldset:';
    const EXPIRE_TABLE_FIELDSET = 864000;
    const FETCH_TYPE_ALL = 0;
    const FETCH_TYPE_ROW = 1;
    const FETCH_TYPE_COLUMN = 2;
    
    protected function __construct() {
        $this->loadDb();
        $this->loadTableFields();
    }
    
    // abstract protected function setTable($sTable) {
    //     $this->sTable = $sTable;
    // }
    
    protected function getPdo($sDb = 'MYSQL') {
        if (!isset($this->aPdos[$sDb])) {
            $this->aPdos[$sDb] = Fac_SysDb::getIns()->loadPdo($sDb);
        }
        return $this->aPdos[$sDb];
    }
    
    protected function loadDb() {
        $oPdo = $this->getPdo();
    }
    
    protected function getRedis() {
        if (!isset($this->oRedis)) {
            $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        }
        return $this->oRedis;
    }
    
    protected function loadTableFields() {
        $sTable = $this->sTable;
        if (!isset($this->sTable)) {
            trigger_error("ORM: Table[{$sTable}] name is empty!", E_USER_ERROR);
            return false;
        }
        $sTableCKey = self::KEY_TABLE_FIELDSET . strtolower($this->sDbSlave . ':' . $sTable);
        $sTableFields = $this->getCache($sTableCKey);
        if (empty($sTableFields)) {
            $sSql = "DESC {$sTable}";
            //$sSql = "SELECT * from SysColumns WHERE id=Object_Id('dv_user')";
            $aTableDescTmp = $this->fetch($sSql);
            if (empty($aTableDescTmp)) {
                trigger_error("ORM: Table[{$sTable}] field is empty", E_USER_ERROR);
                return false;
            } else {
                $aTableDesc = array();
                foreach ($aTableDescTmp as $aTDT) {
                    $aTableDesc[$aTDT['Field']] = $aTDT;
                }
                $this->setCache($sTableCKey, json_encode($aTableDesc) , self::EXPIRE_TABLE_FIELDSET);
            }
        } else {
            $aTableDesc = json_decode($sTableFields, true);
        }
        $this->aTableDesc = $aTableDesc;
        return $aTableDesc;
    }
    
    protected function fetch($sSql, $aParams = array() , $iFetchType = self::FETCH_TYPE_ALL) {
        $oPdo = $this->getPdo();
        $oStmt = $oPdo->prepare($sSql);
        $oStmt->execute($aParams);
        $mData = array();
        switch ($iFetchType) {
            case self::FETCH_TYPE_COLUMN:
                $mData = $oStmt->fetchColumn();
            break;
            case self::FETCH_TYPE_ROW:
                $mData = $oStmt->fetch(PDO::FETCH_ASSOC);
                break;
            case self::FETCH_TYPE_ALL:
            default:
                $mData = $oStmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        }
        return $mData;
    }

    protected function execute($sSql, $aParams = array()) {
        $oPdo = $this->getPdo();
        $oStmt = $oPdo->prepare($sSql);
        return $oStmt->execute($aParams);
    }
    
    public function find() {
        $sSql = 'SELECT ' . $this->genFields().' FROM ' . $this->sTable . ' ' .  $this->genFilters();
        return $this->fetch($sSql, $this->aParams, self::FETCH_TYPE_ROW);
    }

    public function findAll() {
        $sSql = 'SELECT ' . $this->genFields().' FROM ' . $this->sTable . ' ' .  $this->genFilters();
        return $this->fetch($sSql, $this->aParams, self::FETCH_TYPE_ALL);   
    }

    public function insert() {
        $aSaveFields = $this->genSaveFields();
        $sSql = "INSERT INTO {$this->sTable} (".implode(',', array_keys($aSaveFields)).") VALUES (".implode(',', array_values($aSaveFields)).")";
        return $this->execute($sSql, $this->aParams);
    }

    public function update() {

    }
    
    public function save() {
        
    }

    public function del() {

    }

    public function limit($sLimit) {
        return $this;
    }
    
    public function filter($sKey, $sValue, $sOpt = '=') {
        $sOpt = strtoupper($sOpt);
        if (!in_array($sOpt, $this->aOpts)) {
            trigger_error("ORM: operater[{$sOpt}] is not found", E_USER_WARNING);
            return $this;
        }
        $this->aFilters[] = array($sKey, $sValue, $sOpt);
        return $this;
    }

    protected function genSql() {

    }

    protected function genFields() {
        return '*';
    }

    protected function genFilters() {
        $aFilters = $this->aFilters;
        if (empty($aFilters)) {
            return '';
        }
        $sFilter = 'WHERE ';
        $aFilTmp = array();
        foreach ($aFilters as $aFilter) {
            $aFilTmp[] = "{$aFilter[0]} {$aFilter[2]} ?";
            $this->aParams[] = $aFilter[1];
        }
        return $sFilter . implode(' AND ', $aFilTmp);
    }

    protected function genSaveFields() {
        $aFields = $this->aFields;
        $aSaveFields = array();
        foreach ($aFields as $sKey => $sVal) {
            $aSaveFields["`{$sKey}`"] = '?';
            $this->aParams[] = $sVal;
        }
        return $aSaveFields;
    }
    
    protected function setCache($sKey, $sValue, $iExpire = 86400) {
        $oRedis = $this->getRedis();
        return $oRedis->setex($sKey, $iExpire, $sValue);
    }
    
    protected function getCache($sKey) {
        return $this->getRedis()->get($sKey);
    }
    
    public function __set($sField, $mVal) {
        if (!isset($this->aTableDesc[$sField])) {
            trigger_error("ORM: Set an unexist field[{$sField}]", E_USER_WARNING);
            return false;
        }
        $this->aFields[$sField] = $mVal;
        return true;
    }
    
    public function __get($sField) {
        return isset($this->aFields[$sField]) ? $this->aFields[$sField] : null;
    }
    
}
