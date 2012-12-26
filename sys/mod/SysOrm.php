<?php
class Mod_SysOrm extends Mod_SysBase {
    
    protected $aPdos;
    protected $sTable = 'notice_mail_table';
    protected $aTableDesc;
    protected $aTableFields;
    protected $sDbSlave = 'SQLSRV';
    protected $sDbMaster = 'SQLSRV';
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
    const FETCH_TYPE_ALL=0;
    const FETCH_TYPE_ROW=1;
    const FETCH_TYPE_COLUMN=2;
    
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
        $sTableCKey = self::KEY_TABLE_FIELDSET .strtolower($this->sDbSlave .':'. $sTable);
        // $sTableFields = $this->getCache($sTableCKey);
        if (empty($sTableFields)) {
            $sSql = "DESC {$sTable}";
            //$sSql = "SELECT * from SysColumns WHERE id=Object_Id('dv_user')";
            $aTableDescTmp = $this->fetch($sSql);
            if (empty($aTableDescTmp)) {
                trigger_error("ORM: Table[{$sTable}] field is empty", E_USER_ERROR);
                return false;
            }else{
                $aTableDesc = array();
                foreach ($aTableDescTmp as $aTDT) {
                    $aTableDesc[$aTDT['Field']] = $aTDT;
                }
                $this->setCache($sTableCKey, json_encode($aTableDesc), self::EXPIRE_TABLE_FIELDSET);
            }
        }else{
            $aTableDesc = json_decode($sTableFields, true);
        }
        $this->aTableDesc = $aTableDesc;
        print_r($aTableDesc);exit;
        return $aTableDesc;
    }

    protected function fetch($sSql, $aParams = array(), $iFetchType = self::FETCH_TYPE_ALL) {
        $oPdo = $this->getPdo();
        $oStmt = $oPdo->prepare($sSql);
        $oStmt->execute($aParams);
        $mData = array();
        switch ($iFetchType) {
            case self::FETCH_TYPE_COLUMN:
                $mData = $oStmt->fetchColumn();
                break;
            case self::FETCH_TYPE_ROW:
                $mData = $oStmt->fetch();
            case self::FETCH_TYPE_ALL:
            default:
                $mData = $oStmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }
        return $mData;
    }
    
    public function find() {
        
    }
    
    public function save() {
        
    }
    
    public function where() {
        
    }
    
    protected function setCache($sKey, $sValue, $iExpire = 86400) {
        $oRedis = $this->getRedis();
        return $oRedis->setex($sKey, $iExpire, $sValue);
    }
    
    protected function getCache($sKey) {
        return $this->getRedis()->get($sKey);
    }
    
}
