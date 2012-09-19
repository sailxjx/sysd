<?php
/**
 * Document: Mail
 * Created on: 2012-8-23, 14:11:39
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Queue_Mail extends Queue_Queue {
    
    protected function beforeMove($aArgs) {
        list($sFrom, $sTo, $sMember, $iNewScore) = $aArgs;
        return $this;
    }
    
}
