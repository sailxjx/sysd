<?php
/**
 * Document: Task_Base
 * Created on: 2012-9-11, 18:06:08
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Task_Base extends Base {
    
    protected $sMod;
    protected $oTask;
    protected $sSplite = '_';
    protected $iInterval = 100000;
    
    protected function __construct() {
        parent::__construct();
        $this->oTask=Fac_Mod::getIns()->loadModTask();
    }
    
}
