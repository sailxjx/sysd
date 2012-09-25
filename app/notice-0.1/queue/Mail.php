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
        }
        return $this;
    }
    
    protected function waitToSend($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_SEND);
        Queue_Log::getIns()->normal($this->sendLog($aArgs) , time())->add();
        return $this;
    }
    
    protected function sendToError($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_ERROR);
        Queue_Log::getIns()->warning($this->sendLog($aArgs) , time())->add();
        return $this;
    }
    
    protected function sendToSucc($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_SUCC);
        Queue_Log::getIns()->normal($this->sendLog($aArgs) , time())->add();
        return $this;
    }
    
    protected function errorToWait($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_WAIT);
        Queue_Log::getIns()->normal($this->sendLog($aArgs) , time())->add();
        return $this;
    }
    
    protected function errorToFail($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $this->changeStatus($iMailId, Const_Mail::S_FAIL);
        Queue_Log::getIns()->error($this->sendLog($aArgs) , time())->add();
        return $this;
    }
    
    protected function changeStatus($iMailId, $iSt) {
        Store_Mail::getIns()->set(array(
            Const_Mail::F_ID => $iMailId,
            Const_Mail::F_STATUS => $iSt,
        ));
        return $this;
    }
    
    protected function sendLog($aArgs) {
        list($sFrom, $sTo, $iMailId, $iNewScore) = $aArgs;
        $oSLog = Store_Log::getIns();
        $aLog = array(
            Const_Log::F_CTIME => time() ,
            Const_Log::F_LOCATION => Const_Log::L_MAILSEND,
            Const_Log::F_OBJECT => json_encode($aArgs) ,
            Const_Log::F_EXTRA => "mail: {$iMailId} has been set from '{$sFrom}' to '{$sTo}' queue"
        );
        return $oSLog->set($aLog);
    }
    
}
