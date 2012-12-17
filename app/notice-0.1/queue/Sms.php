<?php
class Queue_Sms extends Queue_Queue {
    protected $aQueues = array(
        'wait' => array() ,
        'succ' => array() ,
        'error' => array() ,
        'fail' => array() ,
        'send' => array()
    );
    
    protected function afterMove($aArgs) {
        list($sFrom, $sTo, $sMember, $iNewScore) = $aArgs;
        $sFunc = $sFrom . 'To' . ucfirst($sTo);
        if (method_exists($this, $sFunc)) {
            call_user_func(array(
                $this,
                $sFunc
            ) , $aArgs);
        } else {
            $sFunc = 'to' . ucfirst($sTo);
            if (method_exists($this, $sFunc)) {
                call_user_func(array(
                    $this,
                    $sFunc
                ) , $aArgs);
            }
        }
        return $this;
    }
    
    protected function toSend($aArgs) {
        list($sFrom, $sTo, $iSmsId, $iNewScore) = $aArgs;
        $this->changeStatus($iSmsId, Const_Sms::S_SEND);
        $this->sendLog($aArgs);
        return $this;
    }
    
    protected function toError($aArgs) {
        list($sFrom, $sTo, $iSmsId, $iNewScore) = $aArgs;
        $this->changeStatus($iSmsId, Const_Sms::S_ERROR);
        $this->sendLog($aArgs, 'warning');
        return $this;
    }
    
    protected function toSucc($aArgs) {
        list($sFrom, $sTo, $iSmsId, $iNewScore) = $aArgs;
        $this->changeStatus($iSmsId, Const_Sms::S_SUCC);
        $this->sendLog($aArgs);
        return $this;
    }
    
    protected function toWait($aArgs) {
        list($sFrom, $sTo, $iSmsId, $iNewScore) = $aArgs;
        $this->changeStatus($iSmsId, Const_Sms::S_WAIT);
        $this->sendLog($aArgs);
        return $this;
    }
    
    protected function toFail($aArgs) {
        list($sFrom, $sTo, $iSmsId, $iNewScore) = $aArgs;
        $this->changeStatus($iSmsId, Const_Sms::S_FAIL);
        $this->sendLog($aArgs, 'error');
        return $this;
    }
    
    protected function changeStatus($iSmsId, $iSt) {
        Store_Sms::getIns()->set(array(
            Const_Sms::F_ID => $iSmsId,
            Const_Sms::F_STATUS => $iSt,
        ));
        return $this;
    }
    
    protected function sendLog($aArgs, $sQueue = 'normal') {
        list($sFrom, $sTo, $iSmsId, $iNewScore) = $aArgs;
        return Mod_Log::getIns()->$sQueue('SMS: [%t]; MOVE:%m', date('Y-m-d H:i:s') , "set {$iSmsId} from {$sFrom} to {$sTo}");
    }
    
}
