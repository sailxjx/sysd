<?php

abstract class Driver_Mail extends Mod_SysBase {
    
    abstract public function send($aParams);

}