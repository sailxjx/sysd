<?php
class Util_Check {
    public static function isLegalMobile($sMobile){
        return preg_match("/^(1(([35][0-9])|(47)|[8][0126789]))\d{8}$/", $sMobile);
    }
}