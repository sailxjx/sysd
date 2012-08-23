<?php

/**
 * Document: Queue
 * Created on: 2012-8-23, 14:08:50
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Queue_Queue extends Model_Base {

	protected $oRedis;
	protected $sQueue;
	protected $aWait;
	protected $aError;
	protected $aSucc;
	protected $aFail;
	protected $aQueues = array(
		'wait', //正常等待队列
		'succ', //发送成功队列
		'error', //发送错误队列
		'fail'//发送失败队列
	);

	public function __construct() {
		if (!isset($this->sQueue)) {
			trigger_error('code: don\'t forget to set the key of queue', E_USER_ERROR);
		}
		$this->oRedis = Fac_Db::getIns()->loadRedis();
	}

	/**
	 * 初始化队列
	 * @return \Queue_Queue
	 */
	protected function init() {
		foreach ($this->aQueues as $sQue) {
			$sQue = 'a' . ucfirst($sQue);
			if (isset($this->$sQue)) {
				$this->$sQue = null;
			}
		}
		return $this;
	}

	/**
	 *
	 * @param string $name
	 * @param array $args
	 * @return boolean|\Queue_Queue
	 */
	public function __call($name, $args) {
		$name = strtolower($name);
		if (!in_array($name, $this->aQueues)) {
			trigger_error('code: called method not found', E_USER_ERROR);
			return false;
		}
		$this->setQueue($name, $args);
		return $this;
	}

	/**
	 * 进队列
	 */
	public function push() {
		$this->oRedis->multi();
		foreach ($this->aQueues as $sQue) {
			$sPQue = 'a' . ucfirst($sQue);
			$sKFunc = $this->sQueue . ucfirst($sQue);
			if (isset($this->$sPQue)) {
				foreach ($this->$sPQue as $id => $t) {
					$this->oRedis->zadd(Redis_Key::$sKFunc(), $t, $id);
				}
			}
		}
		$this->oRedis->exec();
		$this->init();
		return $this;
	}

	/**
	 * 出队列
	 */
	public function pop() {

	}

	/**
	 * 设置队列值
	 * @param string $name
	 * @param array $args
	 * @return boolean
	 */
	protected function setQueue($name, $args) {
		$sQue = 'a' . ucfirst($name);
		if (is_array($args[0])) {
			foreach ($args[0] as $id => $t) {
				$this->{$sQue}[$id] = $t;
			}
		}
		elseif (isset($args[1])) {
			$this->{$sQue}[$args[0]] = $args[1];
		}
		else {
			return false;
		}
		return true;
	}

}
