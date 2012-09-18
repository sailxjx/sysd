<?php

/**
 * Document: Queue
 * Created on: 2012-8-23, 14:08:50
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Queue_Queue extends Mod_Base {

    protected $oRedis;
    protected $sQueue;
    protected $aQueues = array(
        'wait' => array(), //正常等待队列
        'succ' => array(), //发送成功队列
        'error' => array(), //发送错误队列
        'fail' => array(), //发送失败队列
        'send' => array()//发送中队列
    );

    public function __construct() {
        $this->getQueue();
        $this->oRedis = Fac_Db::getIns()->loadRedis();
    }

    /**
     * 初始化队列
     * @return \Queue_Queue
     */
    protected function reset() {
        foreach ($this->aQueues as $sQue => $aQue) {
            $this->aQueues[$sQue] = array();
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
        if (!key_exists($name, $this->aQueues)) {
            trigger_error('code: called method not found', E_USER_ERROR);
            return false;
        }
        $this->setQueue($name, $args);
        return $this;
    }

    /**
     * 进队列
     */
    public function add() {
        $this->oRedis->multi();
        foreach ($this->aQueues as $sQue => $aQue) {
            $sKFunc = $this->sQueue . ucfirst($sQue);
            foreach ($aQue as $id => $t) {
                $this->oRedis->zadd(Redis_Key::$sKFunc(), $t, $id);
            }
        }
        $this->oRedis->exec();
        $this->reset();
        return $this;
    }

    /**
     * 出队列
     */
    public function rem() {
        $this->oRedis->multi();
        foreach ($this->aQueues as $sQue => $aQue) {
            $sKFunc = $this->sQueue . ucfirst($sQue);
            foreach ($aQue as $id => $t) {
                $this->oRedis->zrem(Redis_Key::$sKFunc(), $id);
            }
        }
        $this->oRedis->exec();
        $this->reset();
        return $this;
    }

    /**
     * 获取queue名
     * @return string
     */
    protected function getQueue() {
        if (!isset($this->sQueue)) {
            list($sPre, $sQueue) = explode('_', get_called_class());
            if (empty($sQueue)) {
                trigger_error('could not find the called queue', E_USER_ERROR);
            }
            $this->sQueue = strtolower($sQueue);
        }
        return $this->sQueue;
    }

    /**
     * 设置队列值
     * @param string $name
     * @param array $args
     * @return boolean
     */
    protected function setQueue($name, $args) {
        if (is_array($args[0])) {
            foreach ($args[0] as $id => $t) {
                $this->aQueues[$name][$id] = $t;
            }
        } elseif (isset($args[1])) {
            $this->aQueues[$name][$args[0]] = $args[1];
        } else {
            return false;
        }
        return true;
    }

}
