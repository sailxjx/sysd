<?php
class Mod_Curl {
    private $oCurl;
    
    /**
     * @return LF_Assassin_Curl
     */
    public function __construct() {
        $this->oCurl = curl_init();
        $this->init();
    }
    
    protected function init() {
        $this->setAttr(CURLOPT_RETURNTRANSFER, 1);
        $this->setAttr(CURLOPT_CONNECTTIMEOUT, 1);
        $this->setAttr(CURLOPT_TIMEOUT, 10);
    }
    
    public function setUrl($sUrl) {
        curl_setopt($this->oCurl, CURLOPT_URL, $sUrl);
        return $this;
    }
    
    public function setAttr($sName, $sValue) {
        curl_setopt($this->oCurl, $sName, $sValue);
        return $this;
    }
    
    public function setTimeout($iTime) {
        $this->setAttr(CURLOPT_TIMEOUT, $iTime);
        return $this;
    }
    
    public function exec() {
        $this->sRes = curl_exec($this->oCurl);
        $this->sCurlInfo = curl_getinfo($this->oCurl);
        return $this;
    }
    
    private $sRes;
    private $sCurlInfo;
    
    public function getRes() {
        return $this->sRes;
    }
    
    public function getCurlInfo() {
        return $this->sCurlInfo;
    }

    public function close() {
        if (isset($this->oCurl)) {
            curl_close($this->oCurl);
            unset($this->oCurl);
            return true;
        }
        return false;
    }
    
    public function __destruct() {
        $this->close();
    }
}
