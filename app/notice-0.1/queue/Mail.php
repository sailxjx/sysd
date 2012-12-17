<?php
/**
 * Document: Mail
 * Created on: 2012-8-23, 14:11:39
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Queue_Mail extends Queue_Queue {
    
    protected $aQueues = array(
        'wait' => array() , //正常等待队列
        'succ' => array() , //发送成功队列
        'error' => array() , //发送错误队列
        'fail' => array() , //发送失败队列
        'send' => array() , //发送中队列
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
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_SEND);
        $this->sendLog($aArgs);
        return $this;
    }
    
    protected function toError($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_ERROR);
        $this->sendLog($aArgs, 'warning');
        return $this;
    }
    
    protected function toSucc($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_SUCC);
        $this->sendLog($aArgs);
        return $this;
    }
    
    protected function toWait($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_WAIT);
        $this->sendLog($aArgs);
        return $this;
    }
    
    protected function toFail($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_FAIL);
        $this->sendLog($aArgs, 'error');
        return $this;
    }
    
    protected function changeStatus($iMailId, $iSt) {
        Store_Mail::getIns()->set(array(
            Const_Mail::F_ID => $iMailId,
            Const_Mail::F_STATUS => $iSt,
        ));
        return $this;
    }
    
    protected function sendLog($aArgs, $sQueue = 'normal') {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        return Mod_Log::getIns()->$sQueue('MAIL: [%t]; MOVE:%m;', date('Y-m-d H:i:s') , "set {$iMailId} from {$sFrom} to {$sTo}");
    }
    
}
