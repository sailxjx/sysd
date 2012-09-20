<?php

class Driver_MailD extends Driver_Mail {

    public function send($aParams){
        $sSender = $aParams['from'];
        $sReciver = $aParams['to'];
    }

}