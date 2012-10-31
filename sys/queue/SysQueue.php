<?php
abstract class Queue_SysQueue extends Mod_SysBase {
    
    protected $oRedis;
    protected $sQueue;
    protected $aQueues;
    protected $aFuncs = array(
        'beforeAdd',
        'afterAdd',
        'beforeRem',
        'afterRem',
        'beforeMove',
        'afterMove'
    );
    protected $sRKeyClass = 'Redis_SysKey'; //class to build the redis key
    protected $sRExpClass = 'Redis_SysExpire'; //class to get the redis expire
    
    public function __construct() {
        $this->getQueue();
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
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
     * get queue range
     *
     */
    public function range($sQueue, $iStart = 0, $iStop = - 1, $bWithScore = false) {
        $sKFunc = $this->sQueue . ucfirst($sQueue);
        $sRKeyClass = $this->sRKeyClass;
        return $this->oRedis->zrange($sRKeyClass::$sKFunc() , $iStart, $iStop, $bWithScore);
    }
    
    /**
     * add elements into queues
     */
    public function add() {
        $this->beforeAdd();
        $this->oRedis->multi();
        foreach ($this->aQueues as $sQue => $aQue) {
            $sKFunc = $this->sQueue . ucfirst($sQue);
            foreach ($aQue as $id => $t) {
                $sRKeyClass = $this->sRKeyClass;
                $this->oRedis->zadd($sRKeyClass::$sKFunc() , $t, $id);
            }
        }
        $this->oRedis->exec();
        $this->afterAdd();
        $this->reset();
        return $this;
    }
    
    /**
     * rem elements from queues
     */
    public function rem() {
        $this->beforeRem();
        $this->oRedis->multi();
        foreach ($this->aQueues as $sQue => $aQue) {
            $sKFunc = $this->sQueue . ucfirst($sQue);
            foreach ($aQue as $id => $t) {
                $sRKeyClass = $this->sRKeyClass;
                $this->oRedis->zrem($sRKeyClass::$sKFunc() , $id);
            }
        }
        $this->oRedis->exec();
        $this->afterRem();
        $this->reset();
        return $this;
    }
    
    /**
     * move an element from one queue to another
     */
    public function move($sFrom, $sTo, $sMember, $iNewScore) {
        if (!key_exists($sFrom, $this->aQueues) || !key_exists($sTo, $this->aQueues)) {
            trigger_error('error: could not find the called queue', E_USER_WARNING);
            return false;
        }
        $sFromKFunc = $this->sQueue . ucfirst($sFrom);
        $sToKFunc = $this->sQueue . ucfirst($sTo);
        $this->beforeMove(func_get_args());
        $this->oRedis->multi();
        $sRKeyClass = $this->sRKeyClass;
        $this->oRedis->zrem($sRKeyClass::$sFromKFunc() , $sMember);
        $this->oRedis->zadd($sRKeyClass::$sToKFunc() , $iNewScore, $sMember);
        $this->oRedis->exec();
        $this->afterMove(func_get_args());
        $this->reset();
        return $this;
    }
    
    /**
     * 获取queue名
     * @return string
     */
    public function getQueue() {
        if (!isset($this->sQueue)) {
            list($sPre, $sQueue) = explode('_', get_called_class());
            if (empty($sQueue)) {
                trigger_error('error: could not find the called queue', E_USER_ERROR);
            }
            $this->sQueue = $sQueue;
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
            $this->aQueues[$sName][$aArgs[0]] = 0;
        }
        return true;
    }
    
}
