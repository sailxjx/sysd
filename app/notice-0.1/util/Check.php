<?php
class Util_Check {
    public static function isLegalMobile($sMobile){
        return preg_match("/^1[3458]\d{9}$/", $sMobile);
    }
}