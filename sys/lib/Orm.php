<?php

/**
 * Document: Orm
 * Created on: 2011-8-23, 11:51:47
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * MSN: sailxjx@hotmail.com
 * ===========================
 * @version : 0.1.20110824~
 * search by pks (not completed)
 * ===========================
 * @version : 0.1.20110827~
 * add data
 * ===========================
 * @version : 0.1.20110830
 * add update function ; change Exception to trigger_error
 * ===========================
 * @version : 0.1.20110902
 * add delete function ; add find by pks
 * ===========================
 * @version : 0.1.20110909
 * add count function
 * ===========================
 * @version : 0.1.20110914
 * add parallel and sort and limit
 * ===========================
 * @version : 0.1.20110918
 * add get list memcache key 
 * @todo get list only support single primary key, update it later
 * ===========================
 * @version : 0.1.20111012
 * add memcache key for get row
 * ===========================
 * @version : 0.1.20111021
 * add mem,sql,value debug info
 * ===========================
 * @version : 0.1.20111024
 * add cache switch
 */
abstract class LF_Assassin_Orm {

    /**
     * orm object
     * @var object
     */
    protected static $objOrm;

    /**
     * the last sql
     * @var string
     */
    protected $strSql;

    /**
     * find by row ( single or multi pks )
     * @var array
     */
    protected $arrPk = array();

    /**
     * find by list ( single or multi pks )
     * @var array
     */
    protected $arrPks = array();

    /**
     * used table name
     * @var string
     */
    protected $strTableName = '';

    /**
     * master db name
     * @var string
     */
    protected $strMasterDb = '';

    /**
     * slave db name
     * @var string
     */
    protected $strSlaveDb = '';

    /**
     * store the table fields by desc
     * @var array
     */
    protected $arrTableFields = array();

    /**
     * count pk ids
     * @var int
     */
    protected $intPksCnt = 0;

    /**
     * the where condition of sql
     * @var string
     */
    protected $strWhere = '';

    /**
     * the filter array seted by user
     * @var array
     */
    protected $arrFilters = array();

    /**
     * the parallel condition, which means 'or'
     * @var array
     */
    protected $arrParallels = array();

    /**
     * the keys to find the cache
     * @var string or array
     */
    protected $mixCacheKey;

    /**
     * set the class name
     * @var string
     */
    protected static $strClassName;

    /**
     * data pool
     * @var array
     */
    protected $arrData = array();

    /**
     * the default pk ids in table
     * @var array
     */
    protected $arrPrePk = array();

    /**
     * offset of the query
     * @var int
     */
    protected $intOffset;

    /**
     * limit of the query
     * @var int
     */
    protected $intLimit;

    /**
     * the sort of query
     * @var array
     */
    protected $arrSort = array();

    /**
     * hash the where condition by md5
     * @var string
     */
    protected $strWhereMd5 = '';
    protected $bolEnableCache = true;


    const ORM_VERSION='ORM_0_1';
    const FILTER_PATTEN_EQUAL='=';
    const FILTER_PATTEN_MORE='>';
    const FILTER_PATTEN_LESS='<';
    const FILTER_PATTEN_IN='IN';
    const FILTER_PATTEN_UNEQUAL='<>';
    const FILTER_PATTEN_LIKE='LIKE';

    const FETCH_TYPE_ALL=0;
    const FETCH_TYPE_ROW=1;
    const FETCH_TYPE_COLUMN=2;

    const TIME_ORM_PK=86400;
    const KEY_ORM_TABLE_FIELD='ORM_TABLE_FIELD_';
    const TIME_ORM_TABLE_FIELD=864000;

    const ACTION_TYPE_INSERT='INSERT';
    const ACTION_TYPE_REPLACE='REPLACE';

    const E_ORM_ERROR=46;

    const SORT_PATTEN_DESC='DESC';
    const SORT_PATTEN_ASC='ASC';

    /**
     * init orm and find table fields
     * @param string $strTableName 
     */
    protected function __construct($strTableName) {
        $this->strTableName = $strTableName;
        $this->setTableField();
    }

    public function __set($strField, $mixValue) {
        $strFieldUpper = strtoupper($strField);
        if (isset($this->arrTableFields)) {
            if (!isset($this->arrTableFields[$strFieldUpper])) {
                trigger_error('ORM : You set an unexpected attribute, field: "' . $strField . '"', E_USER_WARNING);
                return false;
            }
            if ($this->arrTableFields[$strFieldUpper]['Null'] == 'NO' && $this->arrTableFields[$strFieldUpper]['Default'] == null && $mixValue == '') {
                trigger_error('ORM : You set an illegal attribute, field: "' . $strField . '", please set an unempty value', E_USER_WARNING);
                return false;
            }
            if (in_array($strFieldUpper, $this->arrPrePk)) {
                $this->arrPk[$strFieldUpper] = $mixValue;
            }
            $this->arrData[$strFieldUpper] = $mixValue;
            return true;
        }
        throw new Exception('ORM : Orm is not ready', self::E_ORM_ERROR);
        return false;
    }

    public function __get($strField) {
        $strFieldUpper = strtoupper($strField);
        if (isset($this->arrData[$strFieldUpper])) {
            return $this->arrData[$strFieldUpper];
        }
        return false;
    }

    /**
     * get child orm into object list
     * @param string $strTableName
     * @return LF_Assassin_Orm
     */
    public static function &getOrm($strTableName) {
        if (!isset(self::$objOrm[$strTableName])) {
            self::$strClassName = get_called_class();
            self::$objOrm[$strTableName] = new self::$strClassName($strTableName);
        }
        $objOrm = clone self::$objOrm[$strTableName];
        return $objOrm;
    }

    /**
     * get the fields of a table
     * @return bool
     */
    protected function setTableField() {
        if (!isset($this->strTableName)) {
            throw new Exception('ORM : There must be a table name', self::E_ORM_ERROR);
            return false;
        }
        $strSlaveDb = $this->strSlaveDb;
        $strTableName = $this->strTableName;
        $strKeyOrm = self::KEY_ORM_TABLE_FIELD . strtoupper($strSlaveDb) . '_' . strtoupper($strTableName);
        $intTimeOrm = self::TIME_ORM_TABLE_FIELD;
        $arrTableFields = $this->getMem($strKeyOrm);
        if (!$arrTableFields) {
            $strSql = "DESC {$strTableName}";
            $arrTableFields = $this->fetchData($strSql);
            if (empty($arrTableFields)) {
                throw new Exception('ORM : We could not find out the table', self::E_ORM_ERROR);
                return false;
            } else {
                $this->setMem($strKeyOrm, $arrTableFields, $intTimeOrm);
            }
        }
        foreach ($arrTableFields as $arrColumn) {
            if ($arrColumn['Key'] == 'PRI') {
                $this->intPksCnt++;
                $this->arrPrePk[] = strtoupper($arrColumn['Field']);
            }
            $this->arrTableFields[strtoupper($arrColumn['Field'])] = $arrColumn;
        }
        return true;
    }

    public function getTableField() {
        return $this->arrTableFields;
    }

    /**
     * get row by pk id
     * @return array
     */
    protected function findByPk() {
        if ($this->intPksCnt < 1) {
            trigger_error('ORM : There is no pk id', E_USER_WARNING);
            return false;
        }
        $this->getPkMemKey($this->arrPk);
        $arrData = $this->getMem($this->mixCacheKey);
        if ($arrData) {
            return $arrData;
        }
        $this->setFilters($this->arrPk);
        $this->clearParallels();
        $this->clearRange();
        $this->strSql = $this->formatSelectFields() . $this->formatSqlConditions();
        $arrData = $this->fetchData($this->strSql, array_values($this->arrPk), self::FETCH_TYPE_ROW);
        if ($arrData) {
            $this->setMem($this->mixCacheKey, $arrData, self::TIME_ORM_PK);
        }
        return $arrData;
    }

    /**
     * find rows by the given pk ids
     * @todo multi pks
     * @return type 
     */
    public function findByPks() {
        if ($this->intPksCnt < 1 || empty($this->arrPks)) {
            trigger_error('ORM : This table is no pk id or you don\'t give the value of pk ids', E_USER_WARNING);
            return false;
        }
        if ($this->intPksCnt == 1) {
            $arrMemKeys = array();
            $arrMemKeys = $this->getPkMemKeyList($this->arrPrePk[0], $this->arrPks);
            $arrDataMem = array();
            $arrDataMem = $this->getMem($arrMemKeys);
            $arrData = array_values($arrDataMem);
            $arrDataDiff = array_diff($arrMemKeys, array_keys($arrDataMem));
            $arrDataDb = array();
            if (!empty($arrDataDiff)) {
                $this->arrPks = array_keys($arrDataDiff);
                $this->setFilter($this->arrPrePk[0], $this->arrPks, self::FILTER_PATTEN_IN);
                $this->clearParallels();
                $this->clearRange();
                $this->strSql = $this->formatSelectFields() . $this->formatSqlConditions();
                $arrDataDb = $this->fetchData($this->strSql, $this->arrPks, self::FETCH_TYPE_ALL);
                foreach ($this->arrPrePk as $keyAPPk => $valAPPk) {
                    $arrKeyUpper[$valAPPk] = null;
                }
                foreach ($arrDataDb as $keyDDb => $valADDb) {
                    $arrADDbUpper = array_change_key_case($valADDb, CASE_UPPER);
                    $arrPk = array_intersect_key($arrADDbUpper, $arrKeyUpper);
                    $strMemKey = $this->getPkMemKey($arrPk);
                    $this->setMem($strMemKey, $valADDb, self::TIME_ORM_PK);
                }
            }
            $arrData = array_merge($arrData, $arrDataDb);
        }
        if (!empty($this->arrSort)) {
            foreach ($this->arrSort as $keyASort => $valASort) {
                $strSortKey = $keyASort;
                $strSortPatten = strtoupper($valASort);
                break;
            }
            $arrSortKeys = array();
            foreach ($arrData as $keyData => $valData) {
                $valData = array_change_key_case($valData, CASE_UPPER);
                if (!isset($valData[$strSortKey])) {
                    return $arrData;
                }
                $arrSortKeys[] = $valData[$strSortKey];
            }
            if ($strSortPatten == self::SORT_PATTEN_ASC) {
                array_multisort($arrSortKeys, SORT_ASC, $arrData);
            } else {
                array_multisort($arrSortKeys, SORT_DESC, $arrData);
            }
        }
        return $arrData;
    }

    /**
     * get a data row
     * @return array
     */
    public function getRow() {
        if ($this->intPksCnt > 0 && $this->intPksCnt == count($this->arrPk)) {
            return $this->findByPk();
        }
        $this->strSql = $this->formatSelectFields() . $this->formatSqlConditions();
        $strKey = $this->getMemKeyByMd5Condition($this->strWhereMd5, self::FETCH_TYPE_ROW);
        $arrPk = $this->getMem($strKey, self::FETCH_TYPE_ROW);
        if (!$arrPk) {
            $arrParams = $this->handleValue();
            $arrData = $this->fetchData($this->strSql, $arrParams, self::FETCH_TYPE_ROW);
            if ($this->intPksCnt > 0 && !empty($arrData)) {
                $arrDataUpper = array_change_key_case($arrData, CASE_UPPER);
                $arrPk = array();
                foreach ($this->arrPrePk as $valAPPk) {
                    if (isset($arrDataUpper[$valAPPk])) {
                        $arrPk[$valAPPk] = $arrDataUpper[$valAPPk];
                    }
                }
                $this->setMem($strKey, $arrPk, self::TIME_ORM_PK);
            }
            return $arrData;
        } else {
            if (empty($arrPk)) {
                return null;
            }
            foreach ($arrPk as $keyAPk => $valAPk) {
                $this->$keyAPk = $valAPk;
            }
            return $this->findByPk();
        }
    }

    /**
     * Get data list by where conditions
     * @return array
     */
    public function getList() {
        if ($this->intPksCnt > 0 && $this->intPksCnt == count($this->arrPk)) {
            return array($this->findByPk());
        }
        $this->strSql = $this->formatSelectFields() . $this->formatSqlConditions();
        $strKey = $this->getMemKeyByMd5Condition($this->strWhereMd5);
        $arrPks = $this->getMem($strKey);
        if (!$arrPks) {
            $arrParams = $this->handleValue();
            $arrData = $this->fetchData($this->strSql, $arrParams, self::FETCH_TYPE_ALL);
            if ($this->intPksCnt > 0 && !empty($arrData)) {
                if ($this->intPksCnt == 1) {
                    $strKeyUpper = $this->arrPrePk[0];
                    $arrPks = array();
                    foreach ($arrData as $keyAData => $valAData) {
                        $arrDataTmp = array_change_key_case($valAData, CASE_UPPER);
                        $arrPks[] = $arrDataTmp[$strKeyUpper];
                    }
                }
                $this->setMem($strKey, $arrPks, self::TIME_ORM_PK);
            }
            return $arrData;
        } else {
            if (empty($arrPks)) {
                return array();
            }
            $this->setPks($arrPks);
            return $this->findByPks();
        }
    }

    /**
     * handle the data value into array, assist with the formatsqlconditions function
     * @return array
     */
    protected function handleValue() {
        $arrParams = array();
        foreach ($this->arrFilters as $valAFilter) {
            $arrParams[] = $valAFilter[1];
        }
        foreach ($this->arrParallels as $keyAParallel => $valAParallel) {
            foreach ($valAParallel as $valVAParallel) {
                $arrParams[] = $valVAParallel[1];
            }
        }
        return $arrParams;
    }

    /**
     * get the total count by the query by the conditions
     * @return int
     */
    public function getCount() {
        $this->strSql = $this->formatCountFields() . $this->formatSqlConditions();
        $arrParams = $this->handleValue();
        return $this->fetchData($this->strSql, $arrParams, self::FETCH_TYPE_COLUMN);
    }

    /**
     * Get pk ids by where condition query, this is a protected function
     * @param string $strFilterQuery The where condition query
     * @return array
     */
    protected function getPks($strFilterQuery) {
        $strSql = $this->formatSelectFields(true) . $strFilterQuery;
        $arrParams = array();
        foreach ($this->arrFilters as $keyAFilter => $valAFilter) {
            $arrParams[] = $valAFilter[1];
        }
        return $this->fetchData($strSql, $arrParams, self::FETCH_TYPE_ALL);
    }

    /**
     * Add data -> insert or replace
     * @param string $strActionType
     * @return int The number of last id
     */
    public function addData($strActionType=self::ACTION_TYPE_INSERT) {
        if (empty($this->arrData)) {
            trigger_error('ORM : Before add data, you should set some attibutes for ORM', E_USER_WARNING);
            return false;
        }
        foreach ($this->arrTableFields as $keyATField => $valATField) {
            if ($valATField['Null'] == 'NO' && $valATField['Default'] == null && $valATField['Extra'] != 'auto_increment' && !isset($this->arrData[$keyATField])) {
                trigger_error('ORM : Field "' . $valATField['Field'] . '" need to be none empty, please set a value to this attribute', E_USER_WARNING);
                return false;
            }
        }
        $strAddFields = $this->formatAddFields($strActionType);
        $intDataCnt = count($this->arrData);
        $arrParams = array_values($this->arrData);
        $arrMarks = array();
        for ($i = 0; $i < $intDataCnt; $i++) {
            $arrMarks[] = '?';
        }
        $strMarks = implode(',', $arrMarks);
        $this->strSql = $strAddFields . ' VALUES (' . $strMarks . ')';
        return $this->fillData($this->strSql, $arrParams);
    }

    /**
     * Update data in db
     * @return type 
     */
    public function updateData() {
        if (empty($this->arrData)) {
            trigger_error('ORM : Before update data, you should set some attibutes for ORM', E_USER_WARNING);
            return false;
        }
        $strUpdFields = $this->formatUpdField();
        if ($this->intPksCnt > 0 && $this->intPksCnt == count($this->arrPk)) {
            $this->setFilters($this->arrPk);
        } elseif (!empty($this->arrPk)) {
            $this->addFilters($this->arrPk);
        }
        $this->formatSqlConditions();
        $this->strSql = $strUpdFields . $this->strWhere;
        $arrParams = $this->arrData;
        foreach ($this->arrFilters as $keyAFilter => $valAFilter) {
            $arrParams[] = $valAFilter[1];
        }
        $this->updMemByQuery();
        return $this->changeData($this->strSql, array_values($arrParams));
    }

    public function deleteData($bolForce=false) {
        if ($this->intPksCnt > 0 && $this->intPksCnt == count($this->arrPk)) {
            $this->setFilters($this->arrPk);
        } elseif (!empty($this->arrPk)) {
            $this->addFilters($this->arrPk);
        }
        $arrParams = array();
        foreach ($this->arrFilters as $keyAFilter => $valAFilter) {
            $arrParams[] = $valAFilter[1];
        }
        if (empty($this->arrFilters) && !$bolForce) {
            trigger_error('ORM : If you really want to delete the whole data of the table, try $bolForce=true', E_USER_WARNING);
            return false;
        }
        $this->formatSqlConditions();
        $this->strSql = $this->formatDelField() . $this->strWhere;
        $this->updMemByQuery();
        return $this->changeData($this->strSql, $arrParams);
    }

    /**
     * join the filters in where conditions
     * @return string
     */
    protected function formatSqlConditions() {
        if (empty($this->arrFilters) && empty($this->arrParallels) && empty($this->arrSort)) {
            return '';
        }
        $arrFormatFilter = array();
        foreach ($this->arrFilters as $keyFilter => $valFilter) {
            if ($valFilter[0] == self::FILTER_PATTEN_IN && is_array($valFilter[1])) {
                $strMarks = '';
                foreach ($valFilter[1] as $keyVFilter => $valVFilter) {
                    $strMarks.='?,';
                }
                $arrFormatFilter[] = ' `' . $keyFilter . '` ' . $valFilter[0] . ' (' . substr($strMarks, 0, -1) . ') ';
            } else {
                $arrFormatFilter[] = ' `' . $keyFilter . '` ' . $valFilter[0] . ' ? ';
            }
        }
        $arrFormatParallel = array();
        foreach ($this->arrParallels as $keyAParallel => $valAParallel) {
            foreach ($valAParallel as $valVAParallel) {
                if ($valVAParallel[0] == self::FILTER_PATTEN_IN && is_array($valVAParallel[1])) {
                    $strMarks = '';
                    foreach ($valVAParallel as $valVVAParallel) {
                        $strMarks.='?,';
                    }
                    $arrFormatParallel[] = ' `' . $keyAParallel . '` ' . $valVAParallel[0] . ' (' . substr($strMarks, 0, -1) . ') ';
                } else {
                    $arrFormatParallel[] = ' `' . $keyAParallel . '` ' . $valVAParallel[0] . ' ? ';
                }
            }
        }
        $arrFormatSort = array();
        foreach ($this->arrSort as $keyASort => $valASort) {
            $arrFormatSort[] = '`' . $keyASort . '` ' . $valASort;
        }

        $strFilter = implode($arrFormatFilter, 'AND');
        $strParallel = implode($arrFormatParallel, 'OR');
        if (!empty($strFilter) || !empty($strParallel)) {
            $this->strWhere = ' WHERE';
        }
        $this->strWhere.=$strFilter;
        if (!empty($strParallel)) {
            if (!empty($strFilter)) {
                $this->strWhere.=' AND (' . $strParallel . ')';
            } else {
                $this->strWhere.=' (' . $strParallel . ')';
            }
        }
        if (!empty($arrFormatSort)) {
            $this->strWhere.=' ORDER BY ' . implode(' AND ', $arrFormatSort);
        }
        if (isset($this->intOffset) && isset($this->intLimit)) {
            $this->strWhere.=' LIMIT ' . $this->intOffset . ',' . $this->intLimit;
        }
        $this->strWhereMd5 = md5(var_export($this->arrFilters, true));
        return $this->strWhere;
    }

    /**
     * foamat select fileds by setField function
     * @return string
     */
    protected function formatSelectFields($bolIsPk=false) {
        if ($bolIsPk == true && $this->intPksCnt > 0) {
            return 'SELECT ' . implode(',', $this->arrPrePk) . ' FROM ' . $this->strTableName;
        }
        return 'SELECT * FROM ' . $this->strTableName;
    }

    /**
     * Format insert or replace methods query
     * @param string $strActionType
     * @return string 
     */
    protected function formatAddFields($strActionType=self::ACTION_TYPE_INSERT) {
        $strFields = implode(',', array_keys($this->arrData));
        $strAddSql = $strActionType . ' INTO ' . $this->strTableName . ' (' . $strFields . ')';
        return $strAddSql;
    }

    protected function formatCountFields() {
        return 'SELECT COUNT(*) AS CNT FROM ' . $this->strTableName;
    }

    /**
     * Format update fields
     * @return string
     */
    protected function formatUpdField() {
        $arrUpdFields = '';
        foreach ($this->arrData as $keyAData => $valAData) {
            $arrUpdFields[] = $keyAData . ' = ? ';
        }
        $strAddSql = 'UPDATE ' . $this->strTableName . ' SET ' . implode(',', $arrUpdFields);
        return $strAddSql;
    }

    /**
     * Format the delete query, maybe can add sth. into this
     * @return string
     */
    protected function formatDelField() {
        return 'DELETE FROM ' . $this->strTableName;
    }

    /**
     * get data from db
     * @param string $strSql
     * @param array $arrParams
     * @param int $intFetchType 1->fecthAll,2->fetch,3->fetchColumn
     * @return mix
     */
    protected function fetchData($strSql, $arrParams=array(), $intFetchType=self::FETCH_TYPE_ALL) {
        $this->addDebug('ORM SQL [TYPE ' . $intFetchType . ']: ' . $strSql . ';[VALUE]->' . var_export($arrParams, true));
        $objPdo = LF_DB_Pdo::getPdo($this->strSlaveDb);
        $objStat = $objPdo->prepare($strSql);
        $objStat->execute($arrParams);
        $minData = array();
        if (isset($intFetchType)) {
            switch ($intFetchType) {
                case self::FETCH_TYPE_ALL:
                    $mixData = $objStat->fetchAll();
                    break;
                case self::FETCH_TYPE_ROW:
                    $mixData = $objStat->fetch();
                    break;
                case self::FETCH_TYPE_COLUMN:
                    $mixData = $objStat->fetchColumn();
                    break;
            }
        }
        return $mixData;
    }

    /**
     * Insert data into db, if the pk id is exist, it will throw a warning to user
     * @param string $strSql
     * @param array $arrParams
     * @return int The last affected id ( only in auto increment table )
     */
    protected function fillData($strSql, $arrParams=array()) {
        $this->addDebug('ORM SQL : ' . $strSql . ';[VALUE]->' . var_export($arrParams, true));
        $objPdo = LF_DB_Pdo::getPdo($this->strMasterDb);
        $objStat = $objPdo->prepare($strSql);
        $objStat->execute($arrParams);
        return $objPdo->lastInsertId();
    }

    /**
     * Update data into db
     * @param string $strSql
     * @param array $arrParams
     * @return int The affected rows of
     */
    protected function changeData($strSql, $arrParams=array()) {
        $this->addDebug('ORM SQL : ' . $strSql . ';[VALUE]->' . var_export($arrParams, true));
        $objPdo = LF_DB_Pdo::getPdo($this->strMasterDb);
        $objStat = $objPdo->prepare($strSql);
        $objStat->execute($arrParams);
        return $objStat->rowCount();
    }

    /**
     * get memcache
     * @param mix $mixKey
     * @return mix
     */
    protected function getMem($mixKey) {
        if (!$this->bolEnableCache) {
            return null;
        }
        $objMem = LF_Cache_Memcache::getMem();
        global $GDebug;
        if ($GDebug) {
            $arrExt = $this->getMemExt($mixKey);
            if (is_array($mixKey)) {
                foreach ($mixKey as $keyMKey => $valMKey) {
                    $this->addDebug('ORM GET MEM : ' . $valMKey . ';' . json_encode($arrExt[$valMKey . 'Ext'], true));
                }
            } else {
                $this->addDebug('ORM GET MEM : ' . $mixKey . ';' . json_encode($arrExt[$mixKey . 'Ext'], true));
            }
        }
        return $objMem->get($mixKey);
    }

    protected function getMemExt($mixKey) {
        if (!$this->bolEnableCache) {
            return null;
        }
        $objMem = LF_Cache_Memcache::getMem();
        $mixMem = $objMem->getExtend($mixKey);
        if ($mixMem) {
            if (is_array($mixKey)) {
                foreach ($mixMem as $keyAMem => $valAMem) {
                    $arrExt = explode(',', $valAMem);
                    $mixMem[$keyAMem] = array(
                        'BEGIN' => date('Y-m-d H:i:s', $arrExt[0]),
                        'END' => date('Y-m-d H:i:s', $arrExt[0] + $arrExt[1])
                    );
                }
            } else {
                $arrExt = explode(',', $mixMem);
                $mixMem = array(
                    $mixKey . 'Ext' => array(
                        'BEGIN' => date('Y-m-d H:i:s', $arrExt[0]),
                        'END' => date('Y-m-d H:i:s', $arrExt[0] + $arrExt[1])
                    )
                );
            }
        }
        return $mixMem;
    }

    /**
     * set memcache
     * @param str $strKey
     * @param mix $mixValue
     * @param int $intExpireTime
     * @return bool
     */
    protected function setMem($strKey, $mixValue, $intExpireTime) {
        if (!$this->bolEnableCache) {
            return null;
        }
        $objMem = LF_Cache_Memcache::getMem();
        $this->addDebug('ORM SET MEM : [KEY:' . $strKey . '] -> ' . var_export($mixValue, true) . '[TIME]->' . $intExpireTime);
        return $objMem->set($strKey, $mixValue, $intExpireTime);
    }

    /**
     * delete memcache
     * @param mix $mixKey
     * @return bool 
     */
    protected function delMem($mixKey) {
        if (!$this->bolEnableCache) {
            return null;
        }
        $objMem = LF_Cache_Memcache::getMem();
        if (is_array($mixKey)) {
            foreach ($mixKey as $keyMKey => $valMKey) {
                $this->addDebug('ORM DEL MEM : ' . $valMKey);
            }
        } else {
            $this->addDebug('ORM DEL MEM : ' . $mixKey);
        }
        return $objMem->delete($mixKey);
    }

    public static function getBoundedOrms() {
        return static::$objOrm;
    }

    /**
     * Update memcache after update or delete methods
     * @todo async below
     */
    protected function updMemByQuery() {
        $arrPks = $this->getPks($this->strWhere);
        $arrMemKeys = array();
        if (empty($arrPks)) {
            return false;
        }
        foreach ($arrPks as $valAPk) {
            $arrMemKeys[] = $this->getPkMemKey($valAPk);
        }
        return $this->delMem($arrMemKeys);
    }

    /**
     * add a filter in where condition
     * @param string $strField
     * @param mix $mixValue
     * @param string $strPatten default is '='
     * @return bool
     */
    public function addFilter($strField, $mixValue, $strPatten=self::FILTER_PATTEN_EQUAL) {
        $strFieldUpper = strtoupper($strField);
        if (!isset($this->arrTableFields[$strFieldUpper])) {
            trigger_error('ORM : You set an unexpected attribute in filters, field: "' . $strField . '"', E_USER_WARNING);
            return false;
        }
        $this->arrFilters[$strFieldUpper] = array(
            $strPatten,
            $mixValue
        );
        if ($strPatten == self::FILTER_PATTEN_EQUAL && in_array($strFieldUpper, $this->arrPrePk)) {
            $this->$strField = $mixValue;
        }
        return true;
    }

    /**
     * add filters in where condition
     * @param array $arrFilters
     * @param string $strPatten
     * @return bool
     */
    public function addFilters($arrFilters, $strPatten=self::FILTER_PATTEN_EQUAL) {
        if (!isset($arrFilters) || !is_array($arrFilters)) {
            return false;
        }
        foreach ($arrFilters as $keyAFilter => $valAFilter) {
            $this->addFilter($keyAFilter, $valAFilter, $strPatten);
        }
        return true;
    }

    /**
     * set a filter in where condition, this function will clear the former conditions
     * @param string $strKey
     * @param mix $mixValue
     * @param string $strPatten 
     * @return bool
     */
    public function setFilter($strKey, $mixValue, $strPatten=self::FILTER_PATTEN_EQUAL) {
        $this->arrFilters = array();
        return $this->addFilter($strKey, $mixValue, $strPatten);
    }

    /**
     * set filters in where condition,  this function will clear the former conditions
     * @param array $arrFilters
     * @param string $strPatten
     * @return bool
     */
    public function setFilters($arrFilters, $strPatten=self::FILTER_PATTEN_EQUAL) {
        $this->arrFilters = array();
        return $this->addFilters($arrFilters, $strPatten);
    }

    protected function clearFilters() {
        $this->arrFilters = array();
    }

    /**
     * add parallel in where condition
     * @param type $strField
     * @param type $mixValue
     * @param type $strPatten
     * @return bool
     */
    public function addParallel($strField, $mixValue, $strPatten=self::FILTER_PATTEN_EQUAL) {
        $strFieldUpper = strtoupper($strField);
        if (!isset($this->arrTableFields[$strFieldUpper])) {
            trigger_error('ORM : You set an unexpected attribute in filters, field: "' . $strField . '"', E_USER_WARNING);
            return false;
        }
        $this->arrParallels[$strFieldUpper][] = array(
            $strPatten,
            $mixValue
        );
        return true;
    }

    protected function clearParallels() {
        $this->arrParallels = array();
    }

    public function addSort($strField, $strOrder=self::SORT_PATTEN_DESC) {
        $strFieldUpper = strtoupper($strField);
        if (!isset($this->arrTableFields[$strFieldUpper])) {
            trigger_error('ORM : You set an unexpected attribute in filters, field: "' . $strField . '"', E_USER_WARNING);
            return false;
        }
        $this->arrSort[$strFieldUpper] = $strOrder;
        return true;
    }

    /**
     * add pkids for the findByPks function
     * @param type $arrPks 
     */
    public function addPks($arrPks) {
        $this->arrPks[] = $arrPks;
        $this->arrPks = array_unique($this->arrPks);
    }

    /**
     * set pk ids for findbypks function
     * @param array $arrPks
     */
    public function setPks($arrPks) {
        $this->arrPks = array_unique($arrPks);
    }

    public function setRange($intOffset=0, $intLimit=50) {
        $this->intOffset = $intOffset;
        $this->intLimit = $intLimit;
    }

    public function clearRange() {
        $this->intOffset = null;
        $this->intLimit = null;
    }

    public function setSort() {
        $this->arrSort[] = $a;
    }

    public function getLastSql() {
        return $this->strSql;
    }

    protected function getPkMemKey($arrPks) {
        $this->mixCacheKey = 'ORM_' . self::$strClassName . '_' . $this->strTableName . '_';
        foreach ($arrPks as $keyAPk => $valAPk) {
            $arrPkMems[] = $keyAPk . ':' . $valAPk;
        }
        $strPkMem = implode('_', $arrPkMems);
        $this->mixCacheKey .= $strPkMem;
        return $this->mixCacheKey;
    }

    protected function getPkMemKeyList($strPk, $arrValues) {
        $this->mixCacheKey = array();
        $strCacheKeyPre = 'ORM_' . self::$strClassName . '_' . $this->strTableName . '_';
        foreach ($arrValues as $keyAValue => $valAValue) {
            if (is_array($valAValue)) {
                $arrPkMems = array();
                foreach ($valAValue as $keyVAValue => $valVAValue) {
                    $arrPkMems[] = $keyVAValue . ':' . $valVAValue;
                }
                $strMemKey = $strCacheKeyPre . implode('_', $arrPkMems);
            } else {
                $strMemKey = $strCacheKeyPre . $strPk . ':' . $valAValue;
            }
            $this->mixCacheKey[$valAValue] = $strMemKey;
        }
        return $this->mixCacheKey;
    }

    /**
     * get memcache key by the hash of where conditions
     * @param string $strWhereMd5
     * @return string
     */
    protected function getMemKeyByMd5Condition($strWhereMd5, $intFetchType=self::FETCH_TYPE_ALL) {
        $strKey = 'ORM_' . self::$strClassName . '_' . $this->strTableName . '_' . $intFetchType . '_' . $strWhereMd5;
        return $strKey;
    }

    protected function addDebug($strLog) {
        global $GDebug;
        if ($GDebug) {
            LF_Debug::getIns()->addLog($strLog);
        }
    }

}