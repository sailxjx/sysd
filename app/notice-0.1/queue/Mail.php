<?php
/**
 * Document: Mail
 * Created on: 2012-8-23, 14:11:39
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Queue_Mail extends Queue_Queue {
    
    protected function afterMove($aArgs) {
        list($sFrom, $sTo, $sMember, $iNewScore) = $aArgs;
        $sFunc = $sFrom . 'To' . ucfirst($sTo);
        if (method_exists($this, $sFunc)) {
            call_user_func(array(
                $this,
                $sFunc
            ) , $aArgs);
        }
        return $this;
    }
    
    protected function waitToSend($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId,Const_Mail::S_SEND);
        return $this;
    }

    protected function sendToError($aArgs){
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId,Const_Mail::S_ERROR);
        return $this;        
    }

    protected function sendToSucc($aArgs){
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId,Const_Mail::S_SUCC);
        return $this;        
    }

    protected function errorToWait($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId,Const_Mail::S_WAIT);
        return $this;
    }
    
    protected function errorToFail($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId,Const_Mail::S_FAIL);
        return $this;
    }

    protected function changeStatus($iMailId,$iSt){
        Store_Mail::getIns()->set(array(
            Const_Mail::F_ID => $iMailId,
            Const_Mail::F_STATUS => $iSt,
        ));
        return $this;
    }

}
