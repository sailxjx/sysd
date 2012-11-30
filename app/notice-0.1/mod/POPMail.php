<?php
class Mod_POPMail {
    
    protected $sUser;
    protected $sPass;
    protected $sServer;
    protected $iPort;
    protected $oSock;
    protected $iSockErr;
    protected $sSockErr;
    protected $iSockTimeOut = 30;
    protected $bCheckedIn = false;
    
    const E_CONNECT_FAILED = 1;
    const E_CONNECT_ABORT = 2;
    const E_AUTH_USER_ERROR = 3;
    const E_AUTH_PASS_ERROR = 4;
    const E_CMD_LIST_ERROR = 5;
    const E_CMD_RETR_ERROR = 6;
    
    public function __construct($sServer, $iPort, $sUser, $sPass) {
        $this->sServer = $sServer;
        $this->iPort = $iPort;
        $this->sUser = $sUser;
        $this->sPass = $sPass;
    }
    
    public function __destruct() {
        $this->quit();
    }
    
    protected function connect() {
        $oSock = fsockopen($this->sServer, $this->iPort, $this->iSockErr, $this->sSockErr, $this->iSockTimeOut);
        if (!$oSock) {
            throw new Exception("pop server connection failed", self::E_CONNECT_FAILED);
        }
        stream_set_blocking($oSock, true);
        $sMsg = fgets($oSock, 512);
        if (substr($sMsg, 0, 3) != '+OK') {
            throw new Exception($sMsg, self::E_CONNECT_ABORT);
        }
        $this->oSock = $oSock;
        return $this->oSock;
    }
    
    protected function quit() {
        if (isset($this->oSock)) {
            fputs($this->oSock, "QUIT\r\n");
            unset($this->oSock);
        }
        return true;
    }
    
    protected function authCheck() {
        $oSock = $this->connect();
        fputs($oSock, 'USER ' . $this->sUser . "\r\n");
        $sMsg = fgets($oSock, 512);
        if (substr($sMsg, 0, 3) != '+OK') {
            throw new Exception($sMsg, self::E_AUTH_USER_ERROR);
        }
        fputs($oSock, 'PASS ' . $this->sPass . "\r\n");
        $sMsg = fgets($oSock, 512);
        if (substr($sMsg, 0, 3) != '+OK') {
            throw new Exception($sMsg, self::E_AUTH_PASS_ERROR);
        }
        $this->bCheckedIn = true;
        return $this->oSock;
    }
    
    public function listMail() {
        $oSock = $this->authCheck();
        fputs($oSock, "LIST\r\n");
        $sMsg = fgets($oSock, 512);
        if (substr($sMsg, 0, 3) != '+OK') {
            throw new Exception($sMsg, self::E_CMD_LIST_ERROR);
        }
        $aMailList = array();
        while (substr($sLine = fgets($oSock, 512) , 0, 1) !== '.') {
            $aMailList[] = trim($sLine);
        }
        $this->quit();
        return $aMailList;
    }
    
    public function retrMail($iNo) {
        $oSock = $this->authCheck();
        fputs($oSock, "RETR $iNo\r\n");
        $sMsg = fgets($oSock, 512);
        if (substr($sMsg, 0, 3) != '+OK') {
            throw new Exception($sMsg, self::E_CMD_RETR_ERROR);
        }
        $sMail = '';
        while (substr($sLine = fgets($oSock, 1024) , 0, 1) !== '.') {
            $sMail.= $sLine;
        }
        $this->quit();
        return $sMail;
    }
}
