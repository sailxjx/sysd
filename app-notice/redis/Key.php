<?php

/**
 * Document: Key
 * Created on: 2012-8-22, 16:48:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Redis_Key {

	protected static $aMap = array(
		'mailid' => 'notice:mail:id',
		'mailwait' => 'notice:mail:wait',
		'mailsend' => 'notice:mail:send',
		'mailfail' => 'notice:mail:fail',
		'mailerror' => 'notice:mail:error',
	);

	public static function __callStatic($name, $arguments) {
		if (!isset(self::$aMap[$name])) {
			trigger_error('code: called method not found', E_USER_ERROR);
			return false;
		}
		return self::$aMap[$name];
	}

}
