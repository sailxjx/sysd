<?php

/**
 * Document: Log
 * Created on: 2012-8-23, 14:57:27
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Log_Log extends Model_Base {

	public function add($aLog) {
		$aLog = array_intersect_key(array_change_key_case($aLog), Log_Fields::$aLog);
		unset($aLog['id']);
		if (empty($aLog)) {
			return false;
		}
		$oRedis = Fac_Db::getIns()->loadRedis();
		$iLogId = $oRedis->incr(Redis_Key::logId());
		$sKey = Redis_Key::logTable(array('id' => $iLogId));
		$aLog['id'] = $iLogId;
		$aLog['ctime'] = time();
		$oRedis->hmset($sKey, $aLog);
		$oRedis->expire($sKey, Redis_Expire::LOG);
		return true;
	}

	public function del($id) {
		$oRedis = Fac_Db::getIns()->loadRedis();
		return $oRedis->del(Redis_Key::logTable(array('id' => $id)));
	}

}
