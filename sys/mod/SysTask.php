<?php
/**
 * Document: Task
 * Created on: 2012-9-6, 16:42:38
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Mod_SysTask extends Mod_SysBase {
    
    protected $aMsg = array();
    protected $aChannels = array();
    protected $mChannel = 0;
    protected $sRKeyClass = 'Redis_SysKey'; //class to build the redis key
    
    abstract public function send();
    
    abstract public function recv();
    
    public function channel($mCId = 0) {
        if (!isset($this->aChannels[$mCId])) {
            trigger_error('could not find this channel! ', E_USER_ERROR);
        }
        $this->mChannel = $mCId;
        return $this;
    }
    
    public function msg($sMsg) {
        if ($sMsg) {
            $this->aMsg[] = $sMsg;
        }
        return $this;
    }
    
}
