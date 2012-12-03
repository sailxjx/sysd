<?php
/**
 * Document: Task
 * Created on: 2012-9-6, 16:42:38
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Mod_Task extends Mod_SysBase {
    
    protected $aMsg = array();
    protected $aChannels = array();
    protected $mChannel;
    protected $sRKeyClass = 'Redis_Key'; //class to build the redis key
    
    abstract public function send();
    
    abstract public function recv();
    
    public function channel($mCId = 0) {
        if (is_scalar($mCId) && isset($this->aChannels[$mCId])) {
            $this->mChannel = $this->aChannels[$mCId];
        } elseif ($mCId) {
            $this->mChannel = $mCId;
        }
        return $this;
    }
    
    public function msg($sMsg) {
        if ($sMsg) {
            $this->aMsg[] = $sMsg;
        }
        return $this;
    }
    
}
