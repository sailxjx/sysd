<?php
class Util_Check {
    public static function isLegalMobile($sMobile){
        return preg_match("/^1[34578]\d{9}$/", $sMobile);
    }
}