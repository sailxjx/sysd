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
        'wait' => array() , //正常等待队列
        'succ' => array() , //发送成功队列
        'error' => array() , //发送错误队列
        'fail' => array() , //发送失败队列
        'send' => array() //发送中队列
    );
    protected $aFuncs = array(
        'beforeAdd',
        'afterAdd',
        'beforeRem',
        'afterRem',
        'beforeMove',
        'afterMove'
    );
    
    public function __construct() {
        $this->getQueue();
        $this->oRedis = Fac_Db::getIns()->loadRedis();
        $this->regFuncs();
    }
    
    protected function regFuncs() {
        foreach ($this->aQueues as $sQue => $aQue) {
            $this->aFuncs[] = $sQue;
        }
        $this->aFuncs = array_unique($this->aFuncs);
        return $this;
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
     * @param string $sName
     * @param array $aArgs
     * @return boolean|\Queue_Queue
     */
    public function __call($sName, $aArgs) {
        if (key_exists($sName, $this->aQueues)) {
            $this->setQueue($sName, $aArgs);
        }
        if (!in_array($sName, $this->aFuncs)) {
            trigger_error('error: call an undefined function [' . $sName . ']!', E_USER_ERROR);
            return false;
        }
        return $this;
    }
    
    /**
     * add elements into queues
     */
    public function add() {
        $this->oRedis->multi();
        foreach ($this->aQueues as $sQue => $aQue) {
            $sKFunc = $this->sQueue . ucfirst($sQue);
            $this->beforeAdd($sQue, $aQue);
            foreach ($aQue as $id => $t) {
                $this->oRedis->zadd(Redis_Key::$sKFunc() , $t, $id);
            }
            $this->afterAdd($sQue, $aQue);
        }
        $this->oRedis->exec();
        $this->reset();
        return $this;
    }
    
    /**
     * rem elements from queues
     */
    public function rem() {
        $this->oRedis->multi();
        foreach ($this->aQueues as $sQue => $aQue) {
            $sKFunc = $this->sQueue . ucfirst($sQue);
            $this->beforeRem($sQue, $aQue);
            foreach ($aQue as $id => $t) {
                $this->oRedis->zrem(Redis_Key::$sKFunc() , $id);
            }
            $this->afterRem($sQue, $aQue);
        }
        $this->oRedis->exec();
        $this->reset();
        return $this;
    }
    
    /**
     * move an element from one queue to another
     */
    public function move($sFrom, $sTo, $sMember, $iNewScore) {
        if (!key_exists($sFrom, $this->aQueues) || !key_exists($sTo, $this->aQueues)) {
            trigger_error('error: could not find the called queue', E_USER_ERROR);
            return false;
        }
        $this->oRedis->multi();
        $sFromKFunc = $this->sQueue . ucfirst($sFrom);
        $sToKFunc = $this->sQueue . ucfirst($sTo);
        $this->beforeMove(func_get_args());
        $this->oRedis->zrem(Redis_Key::$sFromKFunc() , $sMember);
        $this->oRedis->zadd(Redis_Key::$sToKFunc() , $iNewScore, $sMember);
        $this->afterMove(func_get_args());
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
                trigger_error('error: could not find the called queue', E_USER_ERROR);
            }
            $this->sQueue = strtolower($sQueue);
        }
        return $this->sQueue;
    }
    
    /**
     * 设置队列值
     * @param string $sName
     * @param array $aArgs
     * @return boolean
     */
    protected function setQueue($sName, $aArgs) {
        if (is_array($aArgs[0])) {
            foreach ($aArgs[0] as $id => $t) {
                $this->aQueues[$sName][$id] = $t;
            }
        } elseif (isset($aArgs[1])) {
            $this->aQueues[$sName][$aArgs[0]] = $aArgs[1];
        } else {
            return false;
        }
        return true;
    }
    
}
