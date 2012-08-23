<?php

/**
 * Document: Val
 * Created on: 2012-8-23, 15:21:20
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Redis_Val extends Model_Base {

	protected $oRedis;

	public function __construct() {
		$this->oRedis = Fac_Db::getIns()->loadRedis();
	}

}