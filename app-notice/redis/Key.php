<?php

/**
 * 获取RedisKey
 * Document: Key
 * Created on: 2012-8-22, 16:48:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Redis_Key {

	protected static $aMap = array(
		'logid' => 'notice:log:id',
		'logtable' => 'notice:log:table:$id',
		'mailid' => 'notice:mail:id',
		'mailwait' => 'notice:mail:wait',
		'mailsend' => 'notice:mail:send',
		'mailfail' => 'notice:mail:fail',
		'mailerror' => 'notice:mail:error',
		'smsid' => 'notice:sms:id'
	);

	public static function __callStatic($name, $args) {
		$name = strtolower($name);
		if (!isset(self::$aMap[$name])) {
			trigger_error('code: called method not found', E_USER_ERROR);
			return false;
		}
		if (isset($args[0])) {
			extract($args[0]);
		}
		$sKey = self::$aMap[$name];
		@eval("\$sKey = \"$sKey\";");
		return $sKey;
	}

}
