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
				throw new Exception('error, db config not found');
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

	/**
	 * 初始化Redis
	 * @param string $sCKey
	 * @return Redis
	 * @throws Exception
	 */
	public function loadRedis($sCKey = 'REDIS') {
		if (!isset($this->aRedis[$sCKey])) {
			$aConf = Util::getConfig($sCKey);
			if (empty($aConf)) {
				throw new Exception('error, redis config not found');
			}
			$oRedis = new Redis();
			if (!$oRedis->pconnect($aConf['host'], $aConf['port'])) {
				throw new Exception('error, redis connection failed');
			}
			$this->aRedis[$sCKey] = $oRedis;
		}
		return $this->aRedis[$sCKey];
	}

}
