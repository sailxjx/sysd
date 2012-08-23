<?php

/**
 * 获取RedisKey
 * Document: Key
 * Created on: 2012-8-22, 16:48:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Redis_Key {

	protected static $sPrefix = 'notice:';
	protected static $aMap = array(
		'logtable' => 'notice:log:table:$id'
	);

	public static function __callStatic($name, $args) {
		$sName = strtolower($name);
		if (!isset(self::$aMap[$sName])) {
			return self::autoKey($name);
		}
		if (isset($args[0])) {
			extract($args[0]);
		}
		$sKey = self::$aMap[$sName];
		@eval("\$sKey = \"$sKey\";");
		return $sKey;
	}

	protected static function autoKey($name) {
		return self::$sPrefix . strtolower(preg_replace('/([a-z])([A-Z])/', '$1:$2', $name));
	}

}

