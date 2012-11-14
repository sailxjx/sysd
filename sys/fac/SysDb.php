<?php
/**
 * Document: Db
 * Created on: 2012-6-4, 12:45:24
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Fac_SysDb {
    
    public function __construct() {
        
    }
    
    public function __destruct() {
        foreach ($this->aPdos as $sCKey => $oPdo) {
            $this->closePdo($sCKey);
        };
    }
    
    protected static $oIns;
    
    /**
     * instance of factory
     * @return Fac_SysDb
     */
    public static function &getIns() {
        if (!isset(self::$oIns)) {
            self::$oIns = new Fac_SysDb();
        }
        return self::$oIns;
    }
    
    protected $aPdos = array();
    protected $aRedis = array();
    
    /**
     * 初始化PDO
     * @param string $sCKey
     * @return PDO
     * @throws Exception
     */
    public function loadPdo($sCKey) {
        if (!isset($this->aPdos[$sCKey])) {
            $aConf = Util::getConfig($sCKey);
            if (empty($aConf)) {
                trigger_error('error, db config not found', E_USER_ERROR);
            }
            $oPdo = new PDO($aConf['dsn'], $aConf['user'], $aConf['pwd'], $aConf['options']);
            if (!empty($aConf['statments'])) {
                foreach ($aConf['statments'] as $sStmt) {
                    $oPdo->exec($sStmt);
                }
            }
            $this->aPdos[$sCKey] = $oPdo;
        }
        return $this->aPdos[$sCKey];
    }
    
    public function closePdo($sCKey) {
        unset($this->aPdos[$sCKey]);
    }
    
    protected $iRedisRetry = 0;
    
    /**
     * 初始化Redis
     * @param string $sCKey
     * @param boolean $bForce
     * @return Redis
     * @throws Exception
     */
    public function loadRedis($sCKey = 'REDIS', $bForce = false) {
        if (!isset($this->aRedis[$sCKey]) && $bForce == false) {
            $aConf = Util::getConfig($sCKey);
            if (empty($aConf)) {
                trigger_error('error, redis config not found', E_USER_ERROR);
            }
            $oRedis = new Redis();
            $oRedis->pconnect($aConf['host'], $aConf['port']);
            $this->aRedis[$sCKey] = $oRedis;
        }
        if (!$this->tRedisConn($this->aRedis[$sCKey])) {
            usleep(100000);
            $this->iRedisRetry++;
            if ($this->iRedisRetry > 3) {
                trigger_error('error, redis connection failed', E_USER_ERROR);
            }
            $this->aRedis[$sCKey] = $this->loadRedis($sCKey, true);
        }
        return $this->aRedis[$sCKey];
    }
    
    protected function tRedisConn(&$oRedis) {
        try {
            if ($oRedis->ping() === '+PONG') {
                return true;
            }
            return false;
        }
        catch(Exception $e) {
            Util::output('redis connect failed');
            return false;
        }
        return false;
    }
    
}
